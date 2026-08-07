/**
 * Artículo destacado enviado por PHP desde la tabla `posts`.
 *
 * No usamos un fallback con contenido inventado porque el bloque
 * "Artículo del día" debe reflejar siempre un artículo real publicado
 * en base de datos.
 *
 * @type {null | {
 *   headline: string,
 *   author: string,
 *   date: string,
 *   categoryPills: string[],
 *   imageAlt: string,
 *   imageSrc: string,
 *   detailUrl: string
 * }}
 */
const featuredArticle = window.CONTEXT_HOME_DATA?.featuredArticle || null;
const recentArticles = window.CONTEXT_HOME_DATA?.recentArticles || [];
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
 * Tarjeta compacta para los artículos más recientes de la portada.
 *
 * @param {{
 *   article: {
 *     headline: string,
 *     author: string,
 *     date: string,
 *     category: string,
 *     excerpt: string,
 *     imageAlt: string,
 *     imageSrc: string,
 *     detailUrl: string
 *   }
 * }} props
 * @returns {JSX.Element}
 */
function RecentArticleCard({ article }) {
    return (
        <article className="recent-articles-card">
            <a href={article.detailUrl} className="recent-articles-card__image-link">
                <img
                    className="recent-articles-card__image"
                    src={article.imageSrc}
                    alt={article.imageAlt}
                />
            </a>

            <div className="recent-articles-card__body">
                <CategoryPill label={article.category} />

                <h3 className="recent-articles-card__headline">
                    <a href={article.detailUrl}>{article.headline}</a>
                </h3>

                <p className="recent-articles-card__excerpt">{article.excerpt}</p>

                <p className="recent-articles-card__meta">
                    Por {article.author} · {article.date}
                </p>
            </div>
        </article>
    );
}

/**
 * Renderiza el bloque de los tres artículos más recientes.
 *
 * @returns {JSX.Element | null}
 */
function RecentArticlesSection() {
    if (!recentArticles.length) {
        return null;
    }

    return (
        <section className="recent-articles-shell">
            <div className="recent-articles-shell__content">
                <div className="featured-article-shell__heading-row">
                    <div>
                        <h2 className="recent-articles-shell__title">
                            <span>Recién publicado;</span>
                        </h2>
                    </div>

                    <a href="articles/view.php" className="featured-article-shell__link">
                        Ver artículos
                        <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div className="recent-articles-grid">
                    {recentArticles.map((article) => (
                        <RecentArticleCard
                            key={`${article.detailUrl}-${article.headline}`}
                            article={article}
                        />
                    ))}
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
    if (!featuredArticle) {
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
                                Ver artículos
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>

                        <p className="article-section-card__empty">
                            Todavía no hay artículos publicados para destacar en portada.
                        </p>
                    </div>
                </section>

                <RecentArticlesSection />
            </>
        );
    }

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

                        <a href={featuredArticle.detailUrl || "articles/view.php"} className="featured-article-shell__link">
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

            <RecentArticlesSection />
        </>
    );
}

const rootElement = document.getElementById("featured-article-root");

if (rootElement) {
    // Montamos React solo si existe el contenedor en la página.
    const root = ReactDOM.createRoot(rootElement);
    root.render(<FeaturedArticleSection />);
}
