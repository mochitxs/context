/**
 * Datos de ejemplo para la portada.
 *
 * En una siguiente fase estos valores pueden llegar desde PHP o una API
 * en formato JSON. Por ahora sirven para demostrar el uso de React en la
 * home sin perder el estilo editorial definido en el proyecto.
 *
 * @type {{
 *   headline: string,
 *   author: string,
 *   date: string,
 *   categoryPills: string[],
 *   imageAlt: string,
 *   imageSrc: string
 * }}
 */
const featuredArticle = {
    headline: "Pilates, clean look, rosa... ¿es esto realmente feminidad?",
    author: "Agata Jiménez",
    date: "20 / 3 / 2026",
    categoryPills: ["Moda", "Identidad", "Opinión"],
    imageAlt: "Collage editorial del artículo del día",
    imageSrc: "assets/images/clean_girl.jpeg"
};

/**
 * Datos de ejemplo para la canción destacada.
 *
 * El campo `spotifyUrl` permite abrir la canción destacada en Spotify.
 *
 * @type {{
 *   title: string,
 *   artist: string,
 *   caption: string,
 *   tags: string[],
 *   spotifyUrl: string
 * }}
 */
const featuredSong = {
    title: "4Real",
    artist: "Nine Vicious",
    caption: "Pick de la administradora :)",
    tags: ["Música", "Cultura"],
    spotifyUrl: "https://open.spotify.com/"
};
/**
 * Mapa de colores para las categorías.
 * Permite asignar un color fijo a cada tipo de contenido.
 */
const tagColorMap = {
    "Moda": "pill--moda",
    "Música": "pill--musica",
    "Sociedad": "pill--sociedad",
    "Cultura": "pill--cultura",
    "Arte": "pill--arte",
    "Identidad": "pill--identidad",
    "Opinión": "pill--opinion"
};

/**
 * Renderiza una etiqueta de categoría reutilizable.
 *
 * El color de la etiqueta depende del tipo de contenido,
 * no de su posición, manteniendo coherencia visual en toda la web.
 *
 * @param {{ label: string }} props
 * @returns {JSX.Element}
 */
function CategoryPill({ label }) {
    const colorClass = tagColorMap[label] || "pill--default";

    return (
        <span className={`tag-pill ${colorClass}`}>
            {label}
        </span>
    );
}

/**
 * Renderiza la imagen principal del artículo destacado.
 *
 * @param {{ src: string, alt: string }} props Datos de la imagen destacada.
 * @returns {JSX.Element} Imagen editorial del artículo del día.
 */
function FeaturedImage({ src, alt }) {
    return (
        <img className="featured-article-card__image" src={src} alt={alt} />
    );
}

/**
 * Renderiza la portada de la canción destacada.
 *
 * Si no hay imagen cargada todavía, muestra un bloque visual provisional
 * para que la estructura de la tarjeta no se rompa.
 *
 * @param {{ alt: string, src: string }} props Datos de la portada.
 * @returns {JSX.Element} Portada visual de la canción.
 */
/**
 * Renderiza un visual editorial para la canción del día.
 *
 * En lugar de depender de una portada real, se genera un bloque visual
 * coherente con la identidad de CONTEXT, evitando que imágenes externas
 * rompan la armonía de la home.
 *
 * @returns {JSX.Element} Visual decorativo de la canción.
 */
function FeaturedSongCover() {
    return (
        <div
            className="featured-song-card__cover featured-song-card__cover--placeholder"
            role="img"
            aria-label="Visual decorativo de la canción del día"
        >
            <div className="featured-song-card__vinyl"></div>
            <div className="featured-song-card__spark"></div>
        </div>
    );
}

/**
 * Renderiza la canción del día.
 *
 * @returns {JSX.Element} Sección destacada de música.
 */
function FeaturedSongSection() {
    return (
        <section className="featured-song-shell">
            <div className="featured-song-shell__content">
                <div className="featured-article-shell__heading-row featured-song-shell__heading-row">
                    <div>
                        <h2 className="featured-song-shell__title">
                            <span>Canción del día;</span>
                        </h2>
                    </div>

                    <a
                        href={featuredSong.spotifyUrl}
                        className="featured-article-shell__link"
                        target="_blank"
                        rel="noreferrer"
                    >
                        Escuchar
                        <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div className="featured-song-card">
                    <FeaturedSongCover
                    />

                    <div className="featured-song-card__body">
                        <p className="featured-song-card__eyebrow">{featuredSong.artist}</p>
                        <h3 className="featured-song-card__title">{featuredSong.title}</h3>
                        <p className="featured-song-card__caption">{featuredSong.caption}</p>

                        <div className="featured-article-card__tags">
                            {featuredSong.tags.map((tag) => (
                                <CategoryPill key={tag} label={tag} />
                            ))}
                        </div>

                       
                    </div>
                </div>
            </div>
        </section>
    );
}

/**
 * Componente principal del bloque "Artículo del día".
 *
 * Este componente reutiliza la estructura editorial del mockup y permite
 * justificar en la defensa por qué React aporta valor en una portada que
 * después tendrá más tarjetas, filtros y secciones destacadas.
 *
 * @returns {JSX.Element} Sección destacada de la home.
 */
function FeaturedArticleSection() {
    return (
        <>
            <section className="featured-article-shell">
                <div className="featured-article-shell__topbar"></div>
                <div className="featured-article-shell__content">
                    <div className="featured-article-shell__heading-row">
                        <div>
                            <h2 className="featured-article-shell__title">
                                <span>Artículo del día;</span>
                            </h2>
                        </div>

                        <a href="articles/view.php" className="featured-article-shell__link">
                            Ver más
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>

                    <FeaturedImage src={featuredArticle.imageSrc} alt={featuredArticle.imageAlt} />

                    <div className="featured-article-card__body">
                        <h3 className="featured-article-card__headline">
                            {featuredArticle.headline}
                        </h3>

                        <p className="featured-article-card__meta">
                            Por {featuredArticle.author} · {featuredArticle.date}
                        </p>

                        <div className="featured-article-card__tags">
                            {featuredArticle.categoryPills.map((pill) => (
                                <CategoryPill key={pill} label={pill} />
                            ))}
                        </div>
                    </div>
                </div>
            </section>

            <FeaturedSongSection />
        </>
    );
}

const rootElement = document.getElementById("featured-article-root");

if (rootElement) {
    const root = ReactDOM.createRoot(rootElement);
    root.render(<FeaturedArticleSection />);
}
