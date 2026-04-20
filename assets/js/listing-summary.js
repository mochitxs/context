/**
 * Resumen visual de listados con React.
 */

function ListingSummaryCard({ label, total, description }) {
    return (
        <section className="mb-14 border-b border-[rgba(75,75,75,0.35)] pb-3">
        <div className="mx-auto w-[min(1120px,calc(100vw-48px))] px-1">

            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                {description ? (
                    <p className="m-0 font-['Playfair_Display'] text-[1rem] italic leading-[1.3] text-[#5d5d5d]">
                        {description}
                    </p>
                ) : (
                    <div></div>
                )}

                <div className="flex items-center gap-3 shrink-0">
                    <span className="font-['Playfair_Display'] text-[1rem] font-bold text-[#111]">
                        {label}
                    </span>

                    <span className="inline-flex h-[30px] min-w-[38px] items-center justify-center rounded-full border border-[#bdd0e8] bg-[#f2b7d8] px-3 font-['Inter'] text-[0.95rem] font-bold text-[#222]">
                        {total}
                    </span>
                </div>
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
