export type MonthNav = {
    previous_month: { month: number; year: number };
    next_month: { month: number; year: number };
};

export function monthQuery(
    nav: { month: number; year: number },
    extra: Record<string, string | number | null | undefined> = {},
): Record<string, string | number> {
    const query: Record<string, string | number> = {
        month: nav.month,
        year: nav.year,
    };

    for (const [key, value] of Object.entries(extra)) {
        if (value !== null && value !== undefined && value !== '') {
            query[key] = value;
        }
    }

    return query;
}
