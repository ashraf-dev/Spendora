export function formatMoney(amount: string | number | null | undefined): string {
    const value = Number(amount ?? 0);

    return new Intl.NumberFormat(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number.isFinite(value) ? value : 0);
}

export function formatPercent(value: number | null | undefined): string {
    const amount = Number(value ?? 0);
    const prefix = amount > 0 ? '+' : '';

    return `${prefix}${amount.toFixed(1)}%`;
}

export function monthLabel(month: number, year: number): string {
    return new Intl.DateTimeFormat(undefined, {
        month: 'long',
        year: 'numeric',
    }).format(new Date(year, month - 1, 1));
}
