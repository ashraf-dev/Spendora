export type CategoryPayload = {
    id: number;
    name: string;
    name_en: string;
    name_ar: string;
    icon: string | null;
    is_active: boolean;
};

export type ExpensePayload = {
    id: number;
    category_id: number;
    expense_date: string;
    description: string | null;
    amount: string;
    category?: CategoryPayload | null;
    created_at?: string;
    updated_at?: string;
};

export type PaginatedExpenses = {
    data: ExpensePayload[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
};

export type CategoryTotalRow = {
    category: CategoryPayload;
    total_amount: string;
    expense_count: number;
    percentage?: number;
    recent_expenses?: ExpensePayload[];
};

export type MonthNavigation = {
    previous_month: { month: number; year: number };
    next_month: { month: number; year: number };
};
