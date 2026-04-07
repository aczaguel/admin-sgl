#!/usr/bin/env python3
"""Generate an inventory of has_permission('...') usages.

Outputs: ACL_PERMISSIONS_INVENTORY.md at repo root.

This is a best-effort static scan:
- 'Action' is inferred by looking for the nearest preceding function definition.
- 'Controller' is inferred by looking for the nearest preceding class definition.
"""

from __future__ import annotations

import re
from dataclasses import dataclass
from pathlib import Path
from typing import Iterable


ROOT = Path(__file__).resolve().parents[1]
APP_DIR = ROOT / "app"
REPORT_PATH = ROOT / "ACL_PERMISSIONS_INVENTORY.md"

PERM_RE = re.compile(r"has_permission\(\s*'([^']+)'")
FUNC_RE = re.compile(r"\bfunction\s+([a-zA-Z0-9_]+)\s*\(")
CLASS_RE = re.compile(r"\bclass\s+([a-zA-Z0-9_]+)\b")


@dataclass(frozen=True)
class Occurrence:
    perm: str
    file: str
    line: int
    kind: str  # controller|view|other
    cls: str | None
    action: str | None


def _kind_for(rel_path: str) -> str:
    if rel_path.startswith("app/Controllers/"):
        return "controller"
    if rel_path.startswith("app/Views/"):
        return "view"
    return "other"


def _nearest_symbol(lines: list[str], idx: int) -> tuple[str | None, str | None]:
    action = None
    for j in range(idx, -1, -1):
        m = FUNC_RE.search(lines[j])
        if m:
            action = m.group(1)
            break

    cls = None
    for j in range(idx, -1, -1):
        m = CLASS_RE.search(lines[j])
        if m:
            cls = m.group(1)
            break

    return cls, action


def iter_occurrences() -> Iterable[Occurrence]:
    for path in APP_DIR.rglob("*.php"):
        rel = path.relative_to(ROOT).as_posix()
        try:
            text = path.read_text(encoding="utf-8", errors="ignore")
        except Exception:
            continue

        lines = text.splitlines()
        for i, line in enumerate(lines):
            m = PERM_RE.search(line)
            if not m:
                continue

            perm = m.group(1)
            cls, action = _nearest_symbol(lines, i)
            yield Occurrence(
                perm=perm,
                file=rel,
                line=i + 1,
                kind=_kind_for(rel),
                cls=cls,
                action=action,
            )


def main() -> int:
    occurrences = list(iter_occurrences())
    perms = sorted({o.perm for o in occurrences})

    by_perm: dict[str, list[Occurrence]] = {p: [] for p in perms}
    for o in occurrences:
        by_perm[o.perm].append(o)

    out: list[str] = []
    out.append("# Inventario de permisos (ACL)\n\n")
    out.append(
        "Este archivo lista cada `has_permission('...')` encontrado en el código y su ubicación.\n"
    )
    out.append(
        "> Nota: “Action” se infiere buscando la función PHP más cercana hacia arriba; es una aproximación.\n\n"
    )

    out.append(f"Permisos distintos: **{len(perms)}**  ")
    out.append(f"Referencias totales: **{len(occurrences)}**\n\n")

    for perm in perms:
        out.append(f"## {perm}\n\n")
        items = sorted(by_perm[perm], key=lambda x: (x.file, x.line))

        controllers = [o for o in items if o.kind == "controller"]
        views = [o for o in items if o.kind == "view"]
        others = [o for o in items if o.kind == "other"]

        def fmt(o: Occurrence) -> str:
            link = f"[{o.file}]({o.file}#L{o.line})"
            if o.kind == "controller":
                where = None
                if o.cls and o.action:
                    where = f"{o.cls}::{o.action}"
                elif o.cls:
                    where = o.cls
                elif o.action:
                    where = o.action
                if where:
                    return f"- {link} — {where}\n"
            return f"- {link}\n"

        if controllers:
            out.append("**Controllers**\n")
            for o in controllers:
                out.append(fmt(o))
            out.append("\n")

        if views:
            out.append("**Vistas**\n")
            for o in views:
                out.append(fmt(o))
            out.append("\n")

        if others:
            out.append("**Otros (helpers, libraries, etc.)**\n")
            for o in others:
                out.append(fmt(o))
            out.append("\n")

    REPORT_PATH.write_text("".join(out), encoding="utf-8")
    print(f"Wrote {REPORT_PATH.relative_to(ROOT)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
