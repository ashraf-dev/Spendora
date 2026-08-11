export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string | null;
    language?: 'en' | 'ar' | string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type AuthLocale = 'en' | 'ar';

export interface AuthCopy {
    common: {
        brand: string;
        home_aria: string;
        language_label: string;
        english: string;
        arabic: string;
        copyright: string;
        legal_navigation: string;
        privacy: string;
        terms: string;
        help: string;
        show_password: string;
        hide_password: string;
    };
    login: {
        meta_title: string;
        meta_description: string;
        title: string;
        description: string;
        email_label: string;
        email_placeholder: string;
        password_label: string;
        password_placeholder: string;
        forgot_password: string;
        remember: string;
        submit: string;
        no_account: string;
        register: string;
    };
    register: {
        meta_title: string;
        meta_description: string;
        title: string;
        description: string;
        name_label: string;
        name_placeholder: string;
        email_label: string;
        email_placeholder: string;
        password_label: string;
        password_placeholder: string;
        password_confirmation_label: string;
        password_confirmation_placeholder: string;
        submit: string;
        has_account: string;
        login: string;
    };
}

/* @chisel-passkeys */
export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};
/* @end-chisel-passkeys */
