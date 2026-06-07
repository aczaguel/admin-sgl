# Plan De Salida De Grocery CRUD

## Conclusion Ejecutiva

Si, es tecnicamente viable replicar la funcionalidad que hoy aporta Grocery CRUD y dejar de depender de el.

Lo que no conviene es plantearlo como un reemplazo generico de una sola vez. En este proyecto Grocery CRUD no es solo un generador de tablas administrativas; tambien esta incrustado dentro de pantallas operativas de tramites, concluidos, cancelados y clientes, donde ademas controla subgrids, uploads, callbacks, acciones custom y endpoints AJAX.

La estrategia correcta es incremental: primero sacar los CRUD administrativos simples, despues encapsular y reemplazar los modulos embebidos de negocio, y al final retirar la dependencia de modelos/utilidades que hoy quedaron acoplados a Grocery CRUD.

## Evidencia Verificada En El Repo

### Superficie De Controladores

- Se detectaron al menos 17 controladores Deskapp que importan `GroceryCrud\\Core\\GroceryCrud`, entre ellos [app/Controllers/Deskapp/Permisos.php](app/Controllers/Deskapp/Permisos.php), [app/Controllers/Deskapp/Users.php](app/Controllers/Deskapp/Users.php), [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php), [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php), [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php), [app/Controllers/Deskapp/Customers.php](app/Controllers/Deskapp/Customers.php) y [app/Controllers/Deskapp/Cancelado.php](app/Controllers/Deskapp/Cancelado.php).
- La dependencia aparece tanto en modulos administrativos pequenos como en flujos core.

### Superficie De Modelos

- Se detectaron al menos 25 modelos propios que importan `GroceryCrud\\Core\\Model` y 32 modelos que extienden `Model`, por ejemplo [app/Models/TramitesModel.php](app/Models/TramitesModel.php), [app/Models/ClienteModel.php](app/Models/ClienteModel.php), [app/Models/GestorModel.php](app/Models/GestorModel.php), [app/Models/TraDocStatusModel.php](app/Models/TraUserLogModel.php) y [app/Models/DocumentsModel.php](app/Models/DocumentsModel.php).
- Esto implica que la salida no es solo de grid/UI; tambien hay acoplamiento en la capa de datos y en convenciones de callbacks.

### Capacidades De Grocery CRUD Que Si Se Estan Usando

- Grids con columnas y labels custom: `columns`, `displayAs`, `callbackColumn`.
- Formularios con mutaciones previas o posteriores: `callbackAddForm`, `callbackEditForm`, `callbackBeforeInsert`, `callbackAfterInsert`, `callbackAfterUpdate`, `callbackAfterDelete`.
- Relaciones simples y N a N: `setRelation`, `setRelationNtoN`.
- Uploads simples y multiples: `setFieldUpload`, `setFieldUploadMultiple`.
- Botones de accion por fila: `setActionButton`.
- Endpoints AJAX por submodulo: `setApiUrlPath`.
- Inyeccion de assets y layout: uso directo de `render()`, `css_files`, `js_files` y customizacion del layout en [app/Controllers/Deskapp/Users.php](app/Controllers/Deskapp/Users.php#L253).

## Clasificacion Por Complejidad

### Grupo A. Reemplazo Relativamente Directo

Son pantallas donde Grocery CRUD opera principalmente como CRUD administrativo con pocas relaciones y callbacks manejables.

- [app/Controllers/Deskapp/Permisos.php](app/Controllers/Deskapp/Permisos.php#L70)
- [app/Controllers/Deskapp/Gestores.php](app/Controllers/Deskapp/Gestores.php#L28)
- [app/Controllers/Deskapp/Cliente.php](app/Controllers/Deskapp/Cliente.php#L28)
- [app/Controllers/Deskapp/Tradocstatus.php](app/Controllers/Deskapp/Tradocstatus.php#L62)
- [app/Controllers/Deskapp/Clidirecto.php](app/Controllers/Deskapp/Clidirecto.php)
- [app/Controllers/Deskapp/Documentos.php](app/Controllers/Deskapp/Documentos.php)

Salida sugerida:

- Reemplazar por controladores y vistas propias con Query Builder o modelos de CodeIgniter.
- Mantener callbacks de auditoria/versionado como servicios explicitos, no como hooks ocultos del grid.

### Grupo B. Reemplazo Medio

Son pantallas administrativas con relaciones mas fuertes, uploads, multiselect o acciones custom, pero sin ser el centro del flujo operativo de tramites.

- [app/Controllers/Deskapp/Users.php](app/Controllers/Deskapp/Users.php#L253)
- [app/Controllers/Deskapp/Roles.php](app/Controllers/Deskapp/Roles.php)
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php)
- [app/Controllers/Deskapp/CorrecionTramites.php](app/Controllers/Deskapp/CorrecionTramites.php)
- [app/Controllers/Deskapp/Bitacora.php](app/Controllers/Deskapp/Bitacora.php)

Salida sugerida:

- Crear componentes propios reutilizables para tabla, formulario, upload y acciones por fila.
- Separar endpoints JSON del render HTML para que el comportamiento deje de depender de `render()`.

### Grupo C. Reemplazo Complejo Y De Alto Riesgo

Aqui Grocery CRUD ya esta incrustado como motor parcial de la pantalla, no solo como tabla. El controlador compone multiples instancias, decide rutas AJAX dinamicas y mezcla permisos/estatus con submodulos vivos.

- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L1918)
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L3014)
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L359)
- [app/Controllers/Deskapp/Customers.php](app/Controllers/Deskapp/Customers.php#L413)
- [app/Controllers/Deskapp/Cancelado.php](app/Controllers/Deskapp/Cancelado.php#L183)
- [app/Controllers/Deskapp/ClienteTramites.php](app/Controllers/Deskapp/ClienteTramites.php#L224)

Ejemplo claro:

- En [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L3014) una sola vista compone un CRUD base mas submodulos para documento status, bitacora, derechos, pago gestor, cobro cliente y evidencias finales, cambiando `setApiUrlPath()` segun estatus y permisos.
- En [app/Controllers/Deskapp/Customers.php](app/Controllers/Deskapp/Customers.php#L413) la pantalla tambien inyecta varias instancias de Grocery CRUD para piezas del proceso, no solo para un listado principal.

Salida sugerida:

- No migrar estas pantallas como "otro CRUD".
- Replantearlas como paginas compuestas con secciones explicitas, endpoints JSON propios y servicios por subdominio.
- Mantener la paridad funcional por slices: documento status, evidencias, pago derechos, pago gestor, cobro cliente, evidencias finales.

## Recomendacion De Arquitectura De Reemplazo

### Fase 1. Crear Un Stack Propio Minimo

- Tabla server-side propia para listados.
- Formularios HTML propios con validacion de CodeIgniter.
- Componente de upload comun.
- Helpers para acciones por fila y relaciones.
- Endpoints JSON explicitos para grids y selectores.

Objetivo:

- Tener infraestructura suficiente para migrar el Grupo A sin introducir otra dependencia grande.

### Fase 2. Sacar Los CRUD Simples

- Empezar por Permisos, Gestores, Cliente y Tradocstatus.
- Medir paridad funcional: alta, edicion, borrado, auditoria, ordenamiento y relaciones.
- Cuando esos modulos funcionen bien, migrar Users y Roles.

Objetivo:

- Reducir rapidamente la huella de Grocery CRUD donde el retorno es alto y el riesgo es bajo.

### Fase 3. Desacoplar Los Modulos Embebidos Del Flujo De Tramites

- Extraer cada submodulo de Tramites/Tramitesn/Concluido/Customers/Cancelado a endpoints y vistas propias.
- Reemplazar cada `setApiUrlPath()` por rutas explicitas del dominio correspondiente.
- Evitar que una sola pantalla cree varias instancias de una libreria de grid para poder funcionar.

Objetivo:

- Convertir el flujo core en paginas compuestas mantenibles, sin dependencia oculta de Grocery CRUD.

### Fase 4. Retirar El Acoplamiento En Modelos

- Sustituir modelos que hoy extienden `GroceryCrud\\Core\\Model` por modelos propios de CodeIgniter o por Query Builder segun el caso.
- Eliminar `_getDbData()` como fabrica de adapters de Grocery CRUD en controladores donde aun exista.

Objetivo:

- Hacer que la salida de Grocery CRUD sea total y no solo cosmetica.

## Relacion Con PHP 8.2

Salir de Grocery CRUD ayuda a la migracion a PHP 8.2, pero no conviene convertirlo en prerequisito total del spike inicial.

Orden recomendado:

1. Confirmar arranque y fallas reales bajo PHP 8.2 con el spike actual.
2. Aislar bloqueos de `system/` y librerias embebidas.
3. Arrancar la salida de Grocery CRUD por el Grupo A, en paralelo o inmediatamente despues del diagnostico ejecutable.

Motivo:

- Si mezclamos una migracion de framework/runtime con un reemplazo total de UI + CRUD + modelos, se vuelve dificil distinguir si una regresion viene por PHP 8.2 o por la reescritura funcional.

## Recomendacion Practica

Si el objetivo inmediato es bajar riesgo tecnico, si conviene empezar a salir de Grocery CRUD.

Pero el primer paso correcto no es reescribir Tramites completo. El primer paso correcto es abrir un PR pequeno de reemplazo para uno de estos modulos: Permisos, Gestores o Cliente. Ese slice nos dira cuanto codigo comun hace falta construir antes de tocar los flujos core.