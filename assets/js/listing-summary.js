/**
 * Resumen visual de listados con React.
 *
 * Este pequeño componente demuestra el uso de React y Tailwind en páginas
 * editoriales más allá de la home sin introducir una complejidad excesiva.
 */

/**
 * Renderiza una cabecera-resumen para una colección editorial.
 *
 * @param {{ label: string, total: number, description: string }} props Datos del bloque.
 * @returns {JSX.Element} Componente React del resumen.
 */
function ListingSummaryCard({ label, total, description }) {
    return (
        <section className="mx-auto mb-6 w-full rounded-[28px] border border-[#bdd0e8] bg-[rgba(248,248,248,0.94)] px-5 py-4 shadow-[0_8px_18px_rgba(0,0,0,0.06)] sm:px-6">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p className="font-['Playfair_Display'] text-[0.95rem] italic text-[#6a584f]">
                        {description}
                    </p>
                </div>

                <div className="inline-flex w-fit items-center gap-3 rounded-full border border-[rgba(102,83,90,0.18)] bg-white px-4 py-2">
                    <span className="font-['Playfair_Display'] text-[1rem] text-[#1f1f1f]">
                        {label}
                    </span>
                    <span className="inline-flex min-w-8 items-center justify-center rounded-full bg-[#ffc0e8] px-2 py-1 text-sm font-semibold text-[#3d3340]">
                        {total}
                    </span>
                </div>
            </div>
        </section>
    );
}

document.querySelectorAll('[data-react-listing-summary]').forEach((node) => {
    const root = ReactDOM.createRoot(node);
    const total = Number(node.dataset.total || 0);
    const label = node.dataset.label || 'items';
    const description = node.dataset.description || '';

    root.render(
        <ListingSummaryCard
            label={label}
            total={total}
            description={description}
        />
    );
});
