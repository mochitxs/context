# context;

Revista digital interactiva pensada como un espacio más reflexivo frente al contenido rápido y sobreestimulante de las redes sociales. Combina contenido editorial con participación activa de la comunidad.

Proyecto final del ciclo de **Desarrollo de Aplicaciones Web**, creado de principio a fin: investigación, diseño UX/UI, frontend, backend, base de datos y despliegue.

🌐 **Demo:** https://agugu.site.je/index.php

> La demo utiliza un hosting gratuito, por lo que la primera carga puede tardar unos segundos.

## Características

- Registro, inicio de sesión y gestión de roles.
- Publicación de posts y artículos.
- Categorías editoriales.
- Comentarios, respuestas y likes.
- Seguimiento de autores.
- Sistema de notificaciones.
- Solicitudes para convertirse en autor.
- Subida de imágenes y personalización del perfil.
- Diseño responsive para ordenadores y móviles.

## Tecnologías

`HTML` · `CSS` · `JavaScript` · `React` · `Tailwind CSS` · `PHP` · `MySQL`

## Instalación local

### 1. Clonar el repositorio

```bash
git clone https://github.com/mochitxs/context.git
cd context
```

### 2. Crear la base de datos

Crea una base de datos llamada `context_db` e importa `context_db.sql`:

```bash
mysql -u root -p context_db < context_db.sql
```

También puedes importar el archivo desde phpMyAdmin.

### 3. Configurar el entorno

Copia el archivo de ejemplo:

```bash
cp .env.example .env
```

Completa en `.env` los datos de conexión con MySQL.

### 4. Iniciar la aplicación

```bash
php -S localhost:8000
```

Abre http://localhost:8000 en el navegador.

## Estado

🟢 Proyecto desplegado y disponible online.

## Autora

**Àgata Jiménez** ༘⋆✧
