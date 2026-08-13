# Guía de Operaciones — SGL Admin

Referencia rápida de comandos frecuentes para operar el sistema en producción.

---

## Conexión al servidor de producción (EC2)

```bash
aws ssm start-session --target i-09e1859ef730d6b7b
```

---

## Actualizar la aplicación en producción

```bash
# Conectarse al EC2
aws ssm start-session --target i-09e1859ef730d6b7b

# En el EC2:
cd /opt/sgl
git pull
docker compose --env-file .env restart
```

---

## Sincronización de archivos al bucket S3

### Migrar archivos del servidor al bucket (primer sync o migración)

```bash
# Desde el EC2 — sube todo lo que no existe en S3 (no borra nada)
aws s3 sync /home/ec2-user/docker-lamp/www/assets/uploads/ s3://bucket-sgl-uploads-dev/ \
  --region us-east-1 \
  --size-only \
  --only-show-errors
```

### Dry-run (ver qué se subiría sin subir nada)

```bash
aws s3 sync /home/ec2-user/docker-lamp/www/assets/uploads/ s3://bucket-sgl-uploads-dev/ \
  --region us-east-1 \
  --size-only \
  --dryrun
```

### Ver contenido del bucket

```bash
aws s3 ls s3://bucket-sgl-uploads-dev/ --region us-east-1
```

### Descargar todos los archivos del bucket al servidor (restore)

```bash
aws s3 sync s3://bucket-sgl-uploads-dev/ /home/ec2-user/docker-lamp/www/assets/uploads/ \
  --region us-east-1 \
  --only-show-errors
```

---

## Migración de la base de datos

```bash
# Aplicar migraciones pendientes (desde dentro del contenedor)
docker exec admin-sgl-app php spark migrate
```

---

## Ver logs de la aplicación

```bash
# Logs del contenedor (últimas 100 líneas)
docker logs admin-sgl-app --tail 100

# Logs en tiempo real
docker logs admin-sgl-app -f

# Logs de CodeIgniter
docker exec admin-sgl-app tail -f /var/www/html/writable/logs/log-$(date +%Y-%m-%d).log
```

---

## Reiniciar servicios

```bash
# Reiniciar solo el contenedor de la app
docker compose --env-file .env restart

# Reconstruir imagen y reiniciar (si cambió el Dockerfile)
docker compose --env-file .env down
DOCKER_BUILDKIT=0 docker build --no-cache -t admin-sgl:php82 .
docker compose --env-file .env up -d
```

---

## Infraestructura Terraform (Prod IaaS)

```bash
cd ~/Sites/admin-sgl/terraform-prod-iaas

# Ver estado actual
terraform show

# Planear cambios
terraform plan

# Aplicar cambios
terraform apply

# Detener el EC2 manualmente (no destruye nada)
aws ec2 stop-instances --instance-ids i-09e1859ef730d6b7b --region us-east-1

# Arrancar el EC2
aws ec2 start-instances --instance-ids i-09e1859ef730d6b7b --region us-east-1
```

---

## Recursos AWS

| Recurso | Valor |
|---|---|
| EC2 Instance ID | `i-09e1859ef730d6b7b` |
| IP Pública (EIP) | `44.212.255.49` |
| Bucket uploads dev | `bucket-sgl-uploads-dev` |
| Bucket state Terraform | `bucket-sgl-terraform-state` |
| DynamoDB locks | `sgl-terraform-locks` |
| Región | `us-east-1` |
| RDS | `sgl-rds-database.cbn6mvubjbj5.us-east-1.rds.amazonaws.com` |
