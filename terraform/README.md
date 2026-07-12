# Terraform — SGL uploads (S3 dev + prod)

Provisiona la infraestructura de almacenamiento de archivos del proyecto:

- **Bucket dev** (`sgl-uploads-dev`) — consumido desde tu máquina local.
- **Bucket prod** (`sgl-uploads-prod`) — consumido por el EC2 vía Instance Profile.
- **IAM user de dev** con política de mínimo privilegio (solo el bucket dev) y su access key para uso local.

Ambos buckets salen del mismo módulo reutilizable `modules/s3_bucket` (SSE AES256, block public access, versioning opcional).

## Estructura

```
terraform/
├── versions.tf        # versión de Terraform + provider AWS (+ backend remoto comentado)
├── providers.tf       # provider aws con default_tags
├── variables.tf       # region, project, nombres de bucket, nombre del IAM user
├── s3.tf              # instancia el módulo dos veces (dev, prod)
├── iam_dev.tf         # IAM user de dev + política least-privilege + access key
├── outputs.tf         # nombres de bucket y credenciales del user de dev (secret sensitive)
├── terraform.tfvars.example
└── modules/
    └── s3_bucket/     # módulo reutilizable de bucket privado
```

## Uso

```bash
cd terraform
cp terraform.tfvars.example terraform.tfvars   # ajusta nombres si están tomados

terraform init
terraform plan
terraform apply
```

Los nombres de bucket S3 son **globales**; si `sgl-uploads-dev` ya existe, cambia
el nombre en `terraform.tfvars` (p. ej. con sufijo de cuenta).

## Después del apply: cablear el contenedor local

```bash
terraform output dev_bucket_name
terraform output dev_access_key_id
terraform output -raw dev_secret_access_key
```

Pega esos valores en `docker/aws.env` (gitignoreado) y en tu `.env` local pon
`FILE_STORAGE_DRIVER = s3` y `S3_BUCKET = <dev_bucket_name>`. Reinicia el
contenedor (`docker compose up -d`) y prueba una subida.

## Seguridad

- El `terraform.tfstate` contiene el **secret** del access key → está gitignoreado.
  Para trabajo en equipo, promueve el state a un **backend remoto S3 + DynamoDB
  lock** (bloque comentado en `versions.tf`). Ese es el patrón correcto y sale en
  la certificación.
- Prod **no** usa access keys: el EC2 autentica con su IAM Instance Profile.
- `docker/aws.env` y `terraform.tfvars` nunca se commitean.

## Siguiente iteración (despliegue completo)

Este stack es la base. Para el despliegue "de un solo golpe" se añadirán módulos
`network` (VPC/SG), `secrets` (secreto RDS), `compute` (EC2 + Instance Profile +
user-data que escribe el `.env`), reutilizando `modules/s3_bucket` tal cual.
