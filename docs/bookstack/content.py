"""
SGL BookStack content definition.

Each entry in BOOKS maps to a BookStack book.
Edit the 'html' fields to update the documentation.
Run seed.py to apply changes to any BookStack instance.
"""

BOOKS = [

# ═══════════════════════════════════════════════════════════════════════════════
# LIBRO 1 — Manual del Ejecutivo
# ═══════════════════════════════════════════════════════════════════════════════
{
    "name": "Manual del Ejecutivo — SGL",
    "description": "Guía completa para el personal ejecutivo sobre el flujo de trámites en el Sistema de Gestión de Licencias (SGL).",
    "chapters": [
        {
            "name": "Introducción al Sistema SGL",
            "description": "Visión general del sistema y sus componentes principales.",
            "pages": [
                {
                    "name": "¿Qué es el Sistema SGL?",
                    "html": """
<h2>¿Qué es el Sistema SGL?</h2>
<p>El Sistema de Gestión de Licencias (SGL) es una plataforma web diseñada para administrar de manera integral el proceso de tramitación de licencias de tránsito vehicular, desde la solicitud inicial hasta la entrega al cliente.</p>
<h3>Objetivo del sistema</h3>
<ul>
<li>Centralizar la operación de trámites vehiculares en un solo sistema.</li>
<li>Dar trazabilidad completa a cada expediente mediante un flujo de 5 pasos.</li>
<li>Facilitar la colaboración entre ejecutivos, gestores y el área de cobro.</li>
<li>Proporcionar visibilidad al cliente sobre el estado de su trámite.</li>
</ul>
<h3>Módulos principales</h3>
<table>
<thead><tr><th>Módulo</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td>Trámites</td><td>Gestión del ciclo de vida completo de cada expediente.</td></tr>
<tr><td>Gestores</td><td>Administración de empresas gestoras y sus representantes.</td></tr>
<tr><td>Clientes</td><td>Portal de consulta para clientes directos.</td></tr>
<tr><td>Cobranza</td><td>Seguimiento de cobro y cierre financiero.</td></tr>
<tr><td>Reportes</td><td>Dashboards y exportaciones para análisis operativo.</td></tr>
</tbody>
</table>
"""
                },
                {
                    "name": "Acceso al Sistema",
                    "html": """
<h2>Acceso al Sistema</h2>
<p>El acceso al SGL se realiza a través del navegador web.</p>
<h3>Requisitos técnicos</h3>
<ul>
<li>Navegador: Google Chrome 100+ o Microsoft Edge 100+ (recomendado).</li>
<li>Conexión a internet estable.</li>
<li>Resolución de pantalla mínima: 1280 × 768 px.</li>
</ul>
<h3>Inicio de sesión</h3>
<ol>
<li>Ingrese su nombre de usuario y contraseña.</li>
<li>Haga clic en <strong>Iniciar sesión</strong>.</li>
<li>El sistema lo redirigirá al dashboard según su perfil.</li>
</ol>
"""
                },
            ]
        },
        {
            "name": "Flujo del Trámite — Paso a Paso",
            "description": "Descripción detallada de los 5 pasos del flujo operativo.",
            "pages": [
                {
                    "name": "Paso 1 — Información General",
                    "html": """
<h2>Paso 1 — Información General del Trámite</h2>
<p>Captura de los datos base del expediente.</p>
<h3>Campos requeridos</h3>
<table>
<thead><tr><th>Campo</th><th>Descripción</th><th>Obligatorio</th></tr></thead>
<tbody>
<tr><td>Contrato</td><td>Número de contrato del cliente.</td><td>Sí</td></tr>
<tr><td>Cliente</td><td>Cliente directo al que pertenece el trámite.</td><td>Sí</td></tr>
<tr><td>Ejecutivo</td><td>Ejecutivo del cliente responsable.</td><td>Sí</td></tr>
<tr><td>Entidad</td><td>Estado donde se realizará el trámite.</td><td>Sí</td></tr>
<tr><td>Unidad / Serie / Placas</td><td>Datos del vehículo.</td><td>No</td></tr>
<tr><td>Observaciones</td><td>Notas internas sobre el expediente.</td><td>No</td></tr>
</tbody>
</table>
"""
                },
                {
                    "name": "Paso 2 — Gestión y Derechos",
                    "html": """
<h2>Paso 2 — Gestión y Derechos</h2>
<p>Asignación del gestor y registro del pago de derechos.</p>
<h3>Botón Aprobar Trámite</h3>
<p>Aparece cuando: gestor asignado + derechos completos + permiso <code>important_pasar_a_pagos</code>.</p>
<p>Al aprobar, el trámite avanza a <em>Evidencias Finales</em> y los pasos 1-2 quedan en modo solo lectura.</p>
"""
                },
                {
                    "name": "Paso 3 — Evidencias Finales",
                    "html": """
<h2>Paso 3 — Evidencias Finales</h2>
<p>Documentos que confirman la entrega del trámite.</p>
<table>
<thead><tr><th>Documento</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td>Trámite Recibido</td><td>Comprobante de entrega del trámite concluido.</td></tr>
<tr><td>Acuse de Recibo del Cliente</td><td>Evidencia de que el cliente recibió la documentación.</td></tr>
</tbody>
</table>
<p>Con ambos documentos, aparece el botón <strong>Aprobar Evidencias Finales</strong> (requiere permiso <code>aprobar_evidencias_finales</code>).</p>
"""
                },
                {
                    "name": "Paso 4 — Pago a Gestor",
                    "html": """
<h2>Paso 4 — Pago a Gestor</h2>
<p>Registro de montos pagados al gestor. Exclusivo del área financiera interna.</p>
<table>
<thead><tr><th>Campo</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td>Costo del trámite</td><td>Costo base de gestión.</td></tr>
<tr><td>Honorarios de gestoría</td><td>Comisión del gestor.</td></tr>
<tr><td>Pago Total</td><td>Suma automática de todos los conceptos.</td></tr>
<tr><td>Depósito a Gestor</td><td>Monto efectivamente pagado.</td></tr>
<tr><td>Saldo Pendiente</td><td>Diferencia automática entre Pago Total y Depósito.</td></tr>
</tbody>
</table>
"""
                },
                {
                    "name": "Paso 5 — Cobro y Cierre",
                    "html": """
<h2>Paso 5 — Cobro y Cierre del Trámite</h2>
<p>Registro del cobro al cliente y conclusión del expediente.</p>
<table>
<thead><tr><th>Campo</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td>Número de factura</td><td>Factura emitida al cliente.</td></tr>
<tr><td>Honorarios del trámite</td><td>Cobro por gestión al cliente.</td></tr>
<tr><td>IVA</td><td>16% calculado automáticamente.</td></tr>
<tr><td>Costo Total</td><td>Suma total a cobrar al cliente.</td></tr>
</tbody>
</table>
<p>Al concluir, el expediente queda en modo de solo lectura permanente.</p>
"""
                },
            ]
        },
    ]
},

# ═══════════════════════════════════════════════════════════════════════════════
# LIBRO 2 — Manual del Closer
# ═══════════════════════════════════════════════════════════════════════════════
{
    "name": "Manual del Closer — SGL",
    "description": "Guía para el perfil Closer sobre el proceso de cobro y cierre de trámites.",
    "chapters": [
        {
            "name": "Introducción al Rol Closer",
            "pages": [
                {
                    "name": "¿Qué es el perfil Closer?",
                    "html": """
<h2>¿Qué es el perfil Closer?</h2>
<p>El Closer es responsable del <strong>cierre financiero y documental</strong> del trámite. Opera principalmente en el Paso 5 — Cobro a Cliente.</p>
<table>
<thead><tr><th>Paso</th><th>Nombre</th><th>Acceso Closer</th></tr></thead>
<tbody>
<tr><td>1-3</td><td>Información, Gestión, Evidencias</td><td>Solo lectura</td></tr>
<tr><td>4</td><td>Pago a Gestor</td><td>Sin acceso</td></tr>
<tr><td>5</td><td>Cobro a Cliente</td><td><strong>Acceso completo</strong></td></tr>
</tbody>
</table>
"""
                },
                {
                    "name": "Cuándo interviene el Closer",
                    "html": """
<h2>Cuándo interviene el Closer</h2>
<p>El Closer puede actuar cuando el trámite alcanza el estatus <strong>Cobro a Cliente</strong>, que se asigna automáticamente cuando:</p>
<ol>
<li>Las evidencias finales fueron aprobadas.</li>
<li>El área financiera registró el pago al gestor con estatus <em>pagado</em>.</li>
<li>La factura y comprobante de pago al gestor están adjuntos.</li>
</ol>
"""
                },
            ]
        },
        {
            "name": "Registro de Cobro a Cliente",
            "pages": [
                {
                    "name": "Formulario de Cobro",
                    "html": """
<h2>Formulario de Cobro a Cliente</h2>
<table>
<thead><tr><th>Campo</th><th>Descripción</th><th>Requerido</th></tr></thead>
<tbody>
<tr><td>ID que da el cliente</td><td>Referencia interna del cliente.</td><td>Sí</td></tr>
<tr><td>Estatus del cobro</td><td>Pendiente, Parcial, Cobrado, etc.</td><td>Sí</td></tr>
<tr><td>Número de factura</td><td>Factura emitida al cliente.</td><td>Sí</td></tr>
<tr><td>Número de refactura</td><td>En caso de corrección.</td><td>No</td></tr>
<tr><td>Honorarios del trámite</td><td>Monto cobrado al cliente.</td><td>Sí</td></tr>
<tr><td>Comisión de derechos</td><td>Comisión adicional.</td><td>Sí</td></tr>
<tr><td>IVA</td><td>16% calculado automáticamente.</td><td>Automático</td></tr>
<tr><td>Costo Total</td><td>Suma total. Calculado automáticamente.</td><td>Automático</td></tr>
</tbody>
</table>
"""
                },
                {
                    "name": "Adjuntar Evidencias de Cobro",
                    "html": """
<h2>Adjuntar Evidencias de Cobro</h2>
<h3>Tipos de evidencia</h3>
<ul>
<li><strong>Cobro parcial:</strong> Pago parcial del cliente.</li>
<li><strong>Cobro completo:</strong> Pago total del cliente.</li>
<li><strong>Otro soporte:</strong> Cualquier otro documento.</li>
</ul>
<h3>Proceso</h3>
<ol>
<li>Seleccione el tipo de soporte.</li>
<li>Arrastre o seleccione el archivo.</li>
<li>Haga clic en <strong>Subir evidencia</strong>.</li>
</ol>
<p>Formatos aceptados: PDF, JPG, PNG, WEBP, GIF, SVG, TIFF, XML.</p>
"""
                },
            ]
        },
        {
            "name": "Conclusión del Trámite",
            "pages": [
                {
                    "name": "Concluir un Trámite",
                    "html": """
<h2>Concluir un Trámite</h2>
<h3>Requisitos previos</h3>
<ul>
<li>Formulario de cobro guardado con campos requeridos completos.</li>
<li>Al menos una evidencia de cobro adjunta.</li>
</ul>
<h3>Proceso</h3>
<ol>
<li>Verifique que todos los datos sean correctos.</li>
<li>Haga clic en <strong>Marcar como Concluido</strong>.</li>
<li>Confirme la acción.</li>
</ol>
<blockquote><strong>Atención:</strong> La conclusión es irreversible. El expediente queda en solo lectura permanente.</blockquote>
"""
                },
            ]
        },
        {
            "name": "Preguntas Frecuentes",
            "pages": [
                {
                    "name": "FAQ del Closer",
                    "html": """
<h2>Preguntas Frecuentes</h2>
<h3>¿Por qué no veo el Paso 5 activo?</h3>
<p>El Paso 5 se habilita cuando el trámite alcanza estatus <em>Cobro a Cliente</em>. Si está en Evidencias Aprobadas, el área financiera aún no ha completado el Paso 4.</p>
<h3>¿Puedo editar el formulario después de guardarlo?</h3>
<p>Sí, hasta que el trámite sea marcado como Concluido o Cancelado.</p>
<h3>¿No veo el botón Guardar Cobro?</h3>
<p>Verifique: estatus <em>Cobro a Cliente</em>, permisos <code>section_final_costos</code> y <code>editar_final</code>, sesión activa.</p>
"""
                },
            ]
        },
    ]
},

# ═══════════════════════════════════════════════════════════════════════════════
# LIBRO 3 — Manual del Cliente
# ═══════════════════════════════════════════════════════════════════════════════
{
    "name": "Manual del Cliente — SGL",
    "description": "Guía para clientes sobre cómo consultar el estado de sus trámites en el portal SGL.",
    "chapters": [
        {
            "name": "Bienvenida al Portal SGL",
            "pages": [
                {
                    "name": "Bienvenido al Portal SGL",
                    "html": """
<h2>Bienvenido al Portal SGL</h2>
<p>El Portal de Cliente le permite consultar el estado de sus trámites vehiculares en tiempo real.</p>
<h3>¿Qué puede hacer?</h3>
<ul>
<li>Ver el listado completo de sus trámites.</li>
<li>Consultar el estado actual de cada expediente.</li>
<li>Ver los documentos adjuntos de cada etapa.</li>
<li>Conocer el gestor asignado.</li>
<li>Ver información de cobro y cierre (perfil Cliente Full).</li>
</ul>
<h3>¿Qué NO puede hacer?</h3>
<ul>
<li>Crear o modificar trámites.</li>
<li>Ver información financiera interna (pagos al gestor).</li>
</ul>
"""
                },
                {
                    "name": "Tipos de Acceso de Cliente",
                    "html": """
<h2>Tipos de Acceso</h2>
<h3>Cliente (Estándar)</h3>
<p>Ve el listado de trámites y el detalle con los Pasos 1, 2 y 3.</p>
<h3>Cliente Full</h3>
<p>Igual que Cliente Estándar, más el <strong>Paso 5 — Cobro y Cierre</strong>: estatus del cobro, número de factura y total del trámite.</p>
<blockquote>Si necesita acceso a información de cobro, solicítelo a su ejecutivo.</blockquote>
"""
                },
            ]
        },
        {
            "name": "Acceso al Portal",
            "pages": [
                {
                    "name": "Inicio de sesión",
                    "html": """
<h2>Inicio de sesión</h2>
<ol>
<li>Abra el navegador (Chrome o Edge recomendado).</li>
<li>Ingrese la URL del portal proporcionada por su ejecutivo.</li>
<li>Introduzca su nombre de usuario y contraseña.</li>
<li>Haga clic en <strong>Iniciar sesión</strong>.</li>
</ol>
<p>Si olvidó su contraseña, contacte a su ejecutivo asignado.</p>
"""
                },
            ]
        },
        {
            "name": "Consulta de Trámites",
            "pages": [
                {
                    "name": "Listado de Trámites",
                    "html": """
<h2>Listado de Trámites</h2>
<p>Muestra todos los trámites asociados a su cuenta. Información visible:</p>
<table>
<thead><tr><th>Columna</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td>Folio</td><td>Identificador único del trámite.</td></tr>
<tr><td>Contrato</td><td>Número de contrato del vehículo.</td></tr>
<tr><td>Tipo de trámite</td><td>Alta, Baja, Reposición, etc.</td></tr>
<tr><td>Entidad</td><td>Estado donde se tramita.</td></tr>
<tr><td>Estatus</td><td>Estado actual del proceso.</td></tr>
</tbody>
</table>
"""
                },
                {
                    "name": "Búsqueda rápida de trámites",
                    "html": """
<h2>Búsqueda rápida</h2>
<p>Use el botón <strong>Busca un trámite</strong> en la barra superior para localizar un expediente por ID, folio o número de contrato.</p>
"""
                },
            ]
        },
        {
            "name": "Información del Trámite",
            "pages": [
                {
                    "name": "Detalle del Trámite",
                    "html": """
<h2>Detalle del Trámite</h2>
<table>
<thead><tr><th>Paso</th><th>Información disponible</th></tr></thead>
<tbody>
<tr><td>Paso 1</td><td>Datos del vehículo, cliente, ejecutivo, entidad, tipo de trámite y documentos del expediente.</td></tr>
<tr><td>Paso 2</td><td>Empresa gestora, nombre del gestor, monto y datos del pago de derechos.</td></tr>
<tr><td>Paso 3</td><td>Estado de las evidencias (Trámite Recibido y Acuse de Recibo) y badge de aprobación.</td></tr>
<tr><td>Paso 5</td><td>Solo para Cliente Full: estatus cobro, factura, refactura, total del trámite.</td></tr>
</tbody>
</table>
"""
                },
            ]
        },
        {
            "name": "Preguntas Frecuentes",
            "pages": [
                {
                    "name": "FAQ del Cliente",
                    "html": """
<h2>Preguntas Frecuentes</h2>
<h3>¿Con qué frecuencia se actualiza el estatus?</h3>
<p>En tiempo real. Recargue la página para ver los cambios más recientes.</p>
<h3>No puedo iniciar sesión. ¿Qué hago?</h3>
<p>Verifique usuario y contraseña. Si persiste, contacte a su ejecutivo.</p>
<h3>No veo ningún trámite. ¿Por qué?</h3>
<p>Su usuario puede no estar asignado al cliente correcto. Contacte al administrador del sistema.</p>
<h3>¿Por qué no veo la información del cobro?</h3>
<p>Requiere perfil <strong>Cliente Full</strong>. Solicítelo a su ejecutivo.</p>
<h3>¿Puedo descargar documentos?</h3>
<p>Sí. Haga clic en el nombre del documento para abrirlo. En el visor encontrará el botón de descarga.</p>
"""
                },
            ]
        },
    ]
},

# ═══════════════════════════════════════════════════════════════════════════════
# LIBRO 4 — Roles, Usuarios y Permisos
# ═══════════════════════════════════════════════════════════════════════════════
{
    "name": "Roles, Usuarios y Permisos — SGL",
    "description": "Documentación detallada sobre la estructura de roles, usuarios y permisos en SGL.",
    "chapters": [
        {
            "name": "Conceptos Fundamentales",
            "pages": [
                {
                    "name": "Arquitectura de Acceso en SGL",
                    "html": """
<h2>Arquitectura de Acceso — RBAC</h2>
<p>SGL implementa control de acceso basado en roles (RBAC).</p>
<table>
<thead><tr><th>Componente</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td><strong>Usuario</strong></td><td>Persona con credenciales únicas.</td></tr>
<tr><td><strong>Rol</strong></td><td>Conjunto predefinido de permisos.</td></tr>
<tr><td><strong>Permiso</strong></td><td>Autorización para una acción específica.</td></tr>
</tbody>
</table>
<h3>Reglas clave</h3>
<ul>
<li>Un usuario puede tener múltiples roles.</li>
<li>Los permisos se acumulan (unión de todos los roles).</li>
<li>Los cambios de permisos aplican en el próximo inicio de sesión.</li>
</ul>
"""
                },
                {
                    "name": "Permisos de Bypass",
                    "html": """
<h2>Permisos de Bypass</h2>
<table>
<thead><tr><th>Permiso</th><th>Efecto</th></tr></thead>
<tbody>
<tr><td><code>override_tramite_approved_lock</code></td><td>Editar pasos 1-3 aunque el trámite esté aprobado.</td></tr>
<tr><td><code>override_tramite_status_28_readonly</code></td><td>Editar documentos en estado Cobro a Cliente.</td></tr>
<tr><td><code>override_puede_editar_modulo</code></td><td>Ignorar reglas de edición por módulo/estatus.</td></tr>
</tbody>
</table>
<blockquote><strong>Precaución:</strong> Asignar estos permisos omite salvaguardas del flujo. Úselos solo cuando sea estrictamente necesario.</blockquote>
"""
                },
            ]
        },
        {
            "name": "Catálogo de Roles",
            "pages": [
                {
                    "name": "Super Admin",
                    "html": """
<h2>Rol: Super Admin</h2>
<p>Acceso irrestricto a todas las funciones. Solo para personal técnico de TI.</p>
<ul>
<li>Puede omitir bloqueos de estatus y aprobaciones.</li>
<li>Acceso a todos los módulos y datos.</li>
<li>Incluye todos los permisos de bypass.</li>
</ul>
<blockquote><strong>Riesgo alto.</strong> Mantener el número de cuentas con este rol al mínimo.</blockquote>
"""
                },
                {
                    "name": "Admin",
                    "html": """
<h2>Rol: Admin</h2>
<p>Para personal gerencial y de supervisión. Acceso amplio dentro del flujo normal.</p>
<ul>
<li>Acceso a todos los módulos operativos.</li>
<li>Puede ver trámites de todos los clientes.</li>
<li>Puede aprobar trámites y evidencias.</li>
<li>Acceso al panel de administración de usuarios.</li>
</ul>
"""
                },
                {
                    "name": "Ejecutivo",
                    "html": """
<h2>Rol: Ejecutivo</h2>
<p>Perfil operativo principal. Crea y gestiona trámites de sus clientes asignados.</p>
<h3>Responsabilidades</h3>
<ul>
<li>Crear nuevos trámites.</li>
<li>Capturar datos del expediente (pasos 1-3).</li>
<li>Asignar gestor y registrar pago de derechos.</li>
<li>Cargar documentos del expediente.</li>
</ul>
<h3>Restricciones</h3>
<ul>
<li>Solo ve trámites de sus clientes asignados.</li>
<li>Sin acceso a información financiera interna.</li>
</ul>
"""
                },
                {
                    "name": "Authorizer y Authorizer Editor",
                    "html": """
<h2>Roles: Authorizer y Authorizer Editor</h2>
<h3>Authorizer</h3>
<ul>
<li>Puede visualizar todos los pasos.</li>
<li>Puede aprobar trámite y evidencias.</li>
<li><strong>No puede editar</strong> datos capturados.</li>
</ul>
<h3>Authorizer Editor</h3>
<ul>
<li>Todo lo del Authorizer.</li>
<li><strong>Además puede editar</strong> los pasos 1-3 antes de aprobar.</li>
</ul>
<h3>Permisos clave</h3>
<ul>
<li><code>important_pasar_a_pagos</code> — Botón Aprobar Trámite.</li>
<li><code>aprobar_evidencias_finales</code> — Botón Aprobar Evidencias.</li>
</ul>
"""
                },
                {
                    "name": "Closer",
                    "html": """
<h2>Rol: Closer</h2>
<p>Especializado en el cierre financiero del trámite.</p>
<ul>
<li>Registra el cobro al cliente (Paso 5).</li>
<li>No tiene acceso al Paso 4 (información financiera interna).</li>
<li>Ve los pasos 1-3 en modo solo lectura.</li>
</ul>
"""
                },
                {
                    "name": "Cliente y Cliente Full",
                    "html": """
<h2>Roles: Cliente y Cliente Full</h2>
<h3>Cliente (Estándar)</h3>
<ul>
<li>Portal de cliente (<code>/deskapp/clientes/cdashboard</code>).</li>
<li>Ve listado de trámites y detalle (Pasos 1, 2, 3).</li>
</ul>
<h3>Cliente Full</h3>
<ul>
<li>Todo lo de Cliente Estándar.</li>
<li>Además ve el <strong>Paso 5</strong>: estatus cobro, factura, refactura, total.</li>
</ul>
<blockquote><strong>Importante:</strong> El usuario debe estar asignado en <code>cliente_user</code> para poder ver trámites.</blockquote>
"""
                },
            ]
        },
        {
            "name": "Catálogo de Permisos",
            "pages": [
                {
                    "name": "Permisos de Trámites — Datos",
                    "html": """
<h2>Permisos de Trámites — Datos y Gestión</h2>
<table>
<thead><tr><th>Permiso</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td><code>editar_tramite</code></td><td>Permiso base para operaciones de escritura en trámites.</td></tr>
<tr><td><code>write_tramite_datos_tramite</code></td><td>Guardar datos base del expediente.</td></tr>
<tr><td><code>write_tramite_asigna_gestor</code></td><td>Asignar empresa gestora y gestor.</td></tr>
<tr><td><code>write_tramite_pago_derechos</code></td><td>Registrar datos del pago de derechos.</td></tr>
<tr><td><code>editar_tramite_principal</code></td><td>Cambiar el tipo de trámite principal.</td></tr>
<tr><td><code>editar_tramite_asociado</code></td><td>Añadir o cambiar tipos de trámite asociados.</td></tr>
<tr><td><code>delete_tramite_asociado</code></td><td>Eliminar tipos de trámite asociados.</td></tr>
</tbody>
</table>
"""
                },
                {
                    "name": "Permisos de Aprobación",
                    "html": """
<h2>Permisos de Aprobación y Flujo</h2>
<table>
<thead><tr><th>Permiso</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td><code>important_pasar_a_pagos</code></td><td>Habilita el botón <strong>Aprobar Trámite</strong>. Avanza a Evidencias Finales.</td></tr>
<tr><td><code>important_ir_pago_gestor</code></td><td>Permite navegar a la sección Pago a Gestor.</td></tr>
<tr><td><code>aprobar_evidencias_finales</code></td><td>Habilita el botón <strong>Aprobar Evidencias Finales</strong>. Desbloquea fase financiera.</td></tr>
</tbody>
</table>
"""
                },
                {
                    "name": "Permisos de Documentos",
                    "html": """
<h2>Permisos de Documentos y Archivos</h2>
<table>
<thead><tr><th>Permiso</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td><code>quick_action_documentos</code></td><td>Acceso a documentos del expediente.</td></tr>
<tr><td><code>quick_action_documentos_add</code></td><td>Subir documentos al expediente.</td></tr>
<tr><td><code>quick_action_documentos_delete</code></td><td>Eliminar documentos del expediente.</td></tr>
<tr><td><code>can_upload_dropzone_pago_derechos</code></td><td>Dropzone de comprobantes de derechos (Paso 2).</td></tr>
<tr><td><code>can_upload_dropzone_evidencias_finales</code></td><td>Subir evidencias finales (Paso 3).</td></tr>
<tr><td><code>can_upload_dropzone_pago_gestor_documentos</code></td><td>Factura y comprobante al gestor (Paso 4).</td></tr>
<tr><td><code>can_upload_dropzone_cobro_cliente</code></td><td>Evidencias de cobro al cliente (Paso 5).</td></tr>
</tbody>
</table>
"""
                },
                {
                    "name": "Permisos Financieros y de Secciones",
                    "html": """
<h2>Permisos de Secciones Financieras</h2>
<table>
<thead><tr><th>Permiso</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td><code>section_pago_gestor</code></td><td>Acceso al Paso 4 (Pago a Gestor). Sin este, el paso está oculto.</td></tr>
<tr><td><code>editar_pago_gestor</code></td><td>Editar campos financieros del Paso 4.</td></tr>
<tr><td><code>section_final_costos</code></td><td>Acceso al Paso 5 (Cobro a Cliente).</td></tr>
<tr><td><code>editar_final</code></td><td>Editar el formulario de cobro en el Paso 5.</td></tr>
<tr><td><code>list_cobro_cliente</code></td><td>Ver el listado de trámites en Cobro a Cliente.</td></tr>
</tbody>
</table>
"""
                },
            ]
        },
        {
            "name": "Gestión de Usuarios",
            "pages": [
                {
                    "name": "Crear un Nuevo Usuario",
                    "html": """
<h2>Crear un Nuevo Usuario</h2>
<ol>
<li>Acceda a <strong>Administración → Usuarios → Nuevo usuario</strong>.</li>
<li>Complete: nombre de usuario, nombre completo, correo y contraseña.</li>
<li>Guarde el usuario.</li>
<li>Asigne los roles correspondientes.</li>
</ol>
<blockquote>Los cambios de roles aplican en el próximo inicio de sesión del usuario.</blockquote>
"""
                },
                {
                    "name": "Asignar Clientes a Usuarios de Tipo Cliente",
                    "html": """
<h2>Asignar Clientes a Usuarios de Tipo Cliente</h2>
<p>Un usuario con rol Cliente solo ve trámites de los clientes que le han sido asignados explícitamente.</p>
<pre>INSERT INTO cliente_user (user_id, cliente_id) 
VALUES ([ID_USUARIO], [ID_CLIENTE]);</pre>
<h3>Verificación</h3>
<pre>SELECT cu.*, u.username, c.nombre AS cliente_nombre
FROM cliente_user cu
JOIN users u ON u.id = cu.user_id
JOIN cliente c ON c.id = cu.cliente_id
WHERE u.username = 'nombre_usuario';</pre>
"""
                },
                {
                    "name": "Desactivar y Eliminar Usuarios",
                    "html": """
<h2>Desactivar y Eliminar Usuarios</h2>
<h3>Desactivar (recomendado)</h3>
<p>Cambie el campo <code>status</code> a <code>0</code>. El usuario no puede iniciar sesión pero su historial se conserva.</p>
<h3>Eliminar</h3>
<p>Acción permanente e irreversible. Solo cuando el usuario no tiene historial relevante.</p>
<blockquote><strong>Siempre prefiera desactivar sobre eliminar.</strong></blockquote>
"""
                },
            ]
        },
        {
            "name": "Casos de Uso y Ejemplos",
            "pages": [
                {
                    "name": "Configuración típica por equipo",
                    "html": """
<h2>Configuración típica: Empresa de gestión de licencias</h2>
<table>
<thead><tr><th>Puesto</th><th>Rol en SGL</th></tr></thead>
<tbody>
<tr><td>Director / Gerente</td><td>Admin</td></tr>
<tr><td>Coordinador de Operaciones</td><td>Authorizer Editor</td></tr>
<tr><td>Ejecutivo de Trámites</td><td>Ejecutivo</td></tr>
<tr><td>Responsable de Cobro</td><td>Closer</td></tr>
<tr><td>Cliente Corporativo</td><td>Cliente Full</td></tr>
<tr><td>Cliente Estándar</td><td>Cliente</td></tr>
<tr><td>Personal de TI</td><td>Super Admin</td></tr>
</tbody>
</table>
"""
                },
                {
                    "name": "Preguntas Frecuentes sobre Permisos",
                    "html": """
<h2>Preguntas Frecuentes sobre Permisos</h2>
<h3>¿Por qué no veo el botón Aprobar Trámite?</h3>
<p>Requiere: gestor asignado + derechos completos + permiso <code>important_pasar_a_pagos</code> + estatus editable.</p>
<h3>¿Por qué el cliente ve error 403?</h3>
<p>Necesita permiso <code>menu_dashboard_cliente</code> y estar asignado en <code>cliente_user</code>. Cierre sesión y vuelva a entrar.</p>
<h3>Cambié permisos pero el usuario no los ve.</h3>
<p>Los permisos se cargan al hacer login. El usuario debe cerrar sesión y volver a ingresar.</p>
<h3>¿Por qué el formulario está en solo lectura?</h3>
<p>Los pasos 1-3 se bloquean al aprobarse el trámite. Para editarlos se necesita el permiso <code>override_tramite_approved_lock</code>.</p>
"""
                },
            ]
        },
    ]
},

]  # end BOOKS
