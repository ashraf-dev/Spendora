import { usePage } from '@inertiajs/vue3';
import { computed, watchEffect } from 'vue';

interface TranslationMessages {
    [key: string]: string | TranslationMessages;
}

export function useTranslations() {
    const page = usePage();
    const locale = computed(() => page.props.locale as string);

    watchEffect(() => {
        if (typeof document === 'undefined') {
            return;
        }

        document.documentElement.lang = locale.value;
        document.documentElement.dir = locale.value === 'ar' ? 'rtl' : 'ltr';
    });

    function t(
        key: string,
        replacements: Record<string, string | number> = {},
    ): string {
        const value = key
            .split('.')
            .reduce<string | TranslationMessages | undefined>(
                (current, segment) => {
                    return typeof current === 'object'
                        ? current[segment]
                        : undefined;
                },
                page.props.translations as TranslationMessages,
            );

        const translated = typeof value === 'string' ? value : key;

        return Object.entries(replacements).reduce(
            (message, [name, replacement]) =>
                message.replaceAll(`:${name}`, String(replacement)),
            translated,
        );
    }

    return { locale, t };
}
