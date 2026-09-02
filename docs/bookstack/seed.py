#!/usr/bin/env python3
"""
BookStack seed script for SGL documentation.

Usage:
    python3 docs/bookstack/seed.py --url http://localhost:8090 --token ID:SECRET

The script is idempotent: it creates books/chapters/pages that don't exist
and updates those that do (matched by name).
"""

import argparse
import sys
import requests

# ── CLI ────────────────────────────────────────────────────────────────────────

parser = argparse.ArgumentParser(description="Seed BookStack with SGL documentation.")
parser.add_argument("--url", default="http://localhost:8090", help="BookStack base URL")
parser.add_argument("--token", required=True, help="Token ID:Token Secret")
args = parser.parse_args()

BASE = args.url.rstrip("/") + "/api"
HEADERS = {
    "Authorization": f"Token {args.token}",
    "Content-Type": "application/json",
}

# ── Helpers ────────────────────────────────────────────────────────────────────

def get_all(endpoint):
    r = requests.get(f"{BASE}/{endpoint}?count=100", headers=HEADERS)
    r.raise_for_status()
    return r.json().get("data", [])

def find_by_name(items, name):
    return next((i for i in items if i["name"] == name), None)

def upsert_book(name, description=""):
    books = get_all("books")
    existing = find_by_name(books, name)
    if existing:
        requests.put(f"{BASE}/books/{existing['id']}", headers=HEADERS,
                     json={"name": name, "description": description})
        return existing["id"]
    r = requests.post(f"{BASE}/books", headers=HEADERS,
                      json={"name": name, "description": description})
    r.raise_for_status()
    return r.json()["id"]

def upsert_chapter(book_id, name, description=""):
    chapters = get_all("chapters")
    book_chapters = [c for c in chapters if c.get("book_id") == book_id]
    existing = find_by_name(book_chapters, name)
    if existing:
        return existing["id"]
    r = requests.post(f"{BASE}/chapters", headers=HEADERS,
                      json={"book_id": book_id, "name": name, "description": description})
    r.raise_for_status()
    return r.json()["id"]

def upsert_page(book_id, chapter_id, name, html):
    pages = get_all("pages")
    chap_pages = [p for p in pages if p.get("chapter_id") == chapter_id]
    existing = find_by_name(chap_pages, name)
    if existing:
        requests.put(f"{BASE}/pages/{existing['id']}", headers=HEADERS,
                     json={"book_id": book_id, "chapter_id": chapter_id,
                           "name": name, "html": html})
        print(f"    ↺ {name[:70]}")
        return existing["id"]
    r = requests.post(f"{BASE}/pages", headers=HEADERS,
                      json={"book_id": book_id, "chapter_id": chapter_id,
                            "name": name, "html": html})
    r.raise_for_status()
    print(f"    ✓ {name[:70]}")
    return r.json()["id"]

# ── Content ────────────────────────────────────────────────────────────────────

def seed_all():
    from docs.bookstack.content import BOOKS
    for book_def in BOOKS:
        print(f"\n{'═'*60}")
        print(f"  {book_def['name']}")
        print(f"{'═'*60}")
        book_id = upsert_book(book_def["name"], book_def.get("description", ""))
        for chap_def in book_def.get("chapters", []):
            chap_id = upsert_chapter(book_id, chap_def["name"], chap_def.get("description", ""))
            print(f"  ▸ {chap_def['name']}")
            for page_def in chap_def.get("pages", []):
                upsert_page(book_id, chap_id, page_def["name"], page_def["html"])
    print("\n✅ Seed completado.")

if __name__ == "__main__":
    # Allow running without the package structure
    import os
    sys.path.insert(0, os.path.join(os.path.dirname(__file__), "../.."))
    try:
        seed_all()
    except KeyboardInterrupt:
        sys.exit(0)
    except requests.exceptions.ConnectionError as e:
        print(f"\n❌ No se pudo conectar a {args.url}")
        print(f"   Verifique que BookStack esté corriendo y que la URL sea correcta.")
        sys.exit(1)
