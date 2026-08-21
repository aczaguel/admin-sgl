# Comandos pendientes — ejecutar cuando termines de probar

> Borrar este archivo una vez ejecutado todo.

---

## 1. Push de cambios locales al repo

```bash
cd ~/Sites/admin-sgl

git add \
  .kiro/specs/tramite-unified-layout/tasks.md \
  terraform-prod-iaas/main.tf \
  terraform-prod-iaas/outputs.tf \
  terraform-prod-iaas/modules/scheduler/ \
  app/Helpers/datetime_es_helper.php \
  app/Libraries/Storage/S3FileStorage.php \
  app/Helpers/filestorage_helper.php \
  app/Controllers/Deskapp/Tramitesn.php \
  app/Views/deskapp/tramite_unified/index.php \
  public/assets/src/styles/tramite_unified_layout.css \
  public/assets/src/js/tramite_unified.js \
  OPERACIONES.md

git commit -m "feat: scheduler, PDF inline, DD/MM/YYYY dates, detailbar, lightbox, grid layout"
git push
```

---

## 2. Actualizar app en el EC2

```bash
aws ssm start-session --target i-09e1859ef730d6b7b
```

```bash
cd /opt/sgl
git pull
docker exec admin-sgl-app php spark migrate
docker compose --env-file .env restart
```

---

## 3. Desplegar scheduler en AWS (terraform apply)

```bash
cd ~/Sites/admin-sgl/terraform-prod-iaas
terraform plan   # revisar que solo agrega Lambda + EventBridge + IAM (~8 recursos)
terraform apply
```

---

## 4. Probar el scheduler manualmente

```bash
# Detener el EC2
aws lambda invoke \
  --function-name sgl-prod-iaas-ec2-scheduler \
  --payload '{"action":"stop"}' \
  --region us-east-1 \
  --cli-binary-format raw-in-base64-out \
  /tmp/response.json && cat /tmp/response.json

# Arrancar el EC2
aws lambda invoke \
  --function-name sgl-prod-iaas-ec2-scheduler \
  --payload '{"action":"start"}' \
  --region us-east-1 \
  --cli-binary-format raw-in-base64-out \
  /tmp/response.json && cat /tmp/response.json
```

---

## 5. Habilitar/deshabilitar el scheduler cuando quieras

Para **deshabilitar** mientras pruebas (sin destruir nada):
```bash
cd ~/Sites/admin-sgl/terraform-prod-iaas
# Edita terraform.tfvars y agrega: scheduler_enabled = false
# O directamente con AWS CLI:
aws events disable-rule --name sgl-prod-iaas-ec2-start --region us-east-1
aws events disable-rule --name sgl-prod-iaas-ec2-stop  --region us-east-1
```

Para **volver a habilitar**:
```bash
aws events enable-rule --name sgl-prod-iaas-ec2-start --region us-east-1
aws events enable-rule --name sgl-prod-iaas-ec2-stop  --region us-east-1
```

---

## 6. Migración de BD (referencia bancaria VARCHAR 100)

Ya se ejecuta en el paso 2 con `php spark migrate`. Verificar:
```bash
docker exec admin-sgl-app php spark migrate:status
```
