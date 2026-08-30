# context;

Revista digital interactiva pensada como un espacio más reflexivo frente al contenido rápido y sobreestimulante de redes sociales. Combina la profundidad de una publicación editorial con participación activa de comunidad.

Proyecto final de ciclo (DAW), desarrollado de principio a fin: investigación, diseño UX/UI, backend, base de datos y testing.

## Características

- **Autenticación y roles** — registro/login con contraseñas hasheadas, sesiones, permisos por rol (visitante, usuario, autor, administrador)
- **Contenido** — artículos y posts con CRUD completo, categorías (arte y cultura, moda y música, sociedad y opinión), subida de imágenes
- **Comunidad** — comentarios con respuestas, likes, sistema de follows entre usuarios, notificaciones
- **Exploración** — sección de descubrimiento de contenido y autores

## Stack técnico

`HTML` · `CSS` · `JavaScript` · `React` (secciones puntuales) · `Tailwind CSS` · `PHP` · `MySQL`

## Cómo instalarlo en local

### Requisitos

- PHP 8+
- MySQL / MariaDB
- Servidor local tipo XAMPP, MAMP o el servidor integrado de PHP

### Pasos

1. **Clona el repositorio**
   ```bash
   git clone https://github.com/mochitxs/context.git
   cd context
   ```

2. **Crea la base de datos**

   Crea una base de datos llamada `context_db` e impórtale el dump incluido:
   ```bash
   mysql -u root -p context_db < context_db.sql
   ```

3. **Configura las variables de entorno**

   Copia la plantilla y ajusta los valores si tu configuración de MySQL no es la de por defecto:
   ```bash
   cp .env.example .env
   ```

4. **Levanta el servidor**

   Con el servidor integrado de PHP, desde la raíz del proyecto:
   ```bash
   php -S localhost:8000
   ```
   O sirve la carpeta `context/` desde `htdocs` si usas XAMPP.

5. Abre `http://localhost:8000` (o la URL que corresponda) en el navegador.

## Estado

🟢 Disponible en local · Despliegue en producción en progreso

## Autora

Àgata — [LinkedIn] · [portfolio]
