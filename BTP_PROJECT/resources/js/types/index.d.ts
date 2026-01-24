export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Auth {
    user: User | null;
    permissions: string[];
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: Auth;
    flash: {
        success?: string | null;
        error?: string | null;
    };
    session: {
        active_organisation_id: number | null;
        active_organisation_name: string | null;
    };
    errors?: Record<string, string>;
};  