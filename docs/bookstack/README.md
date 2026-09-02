# BookStack — Documentación SGL

Scripts para poblar la instancia de BookStack con el contenido de los manuales de usuario del Sistema SGL.

## Requisitos

```bash
pip3 install requests
```

## Uso

### Contra la instancia local
```bash
python3 docs/bookstack/seed.py --url http://localhost:8090 --token TU_TOKEN_ID:TU_TOKEN_SECRET
```

### Contra producción
```bash
python3 docs/bookstack/seed.py --url http://44.212.255.49:8090 --token TU_TOKEN_ID:TU_TOKEN_SECRET
```

## Obtener un token API

1. Inicia sesión en BookStack como Admin.
2. Ve a **Preferencias → Tokens API**.
3. Crea un token nuevo y copia el `Token ID` y el `Token Secret`.

## Comportamiento

El script es **idempotente**: si un libro ya existe con el mismo nombre, actualiza su contenido. Si no existe, lo crea.
