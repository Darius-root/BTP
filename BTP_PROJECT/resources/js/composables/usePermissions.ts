import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

export function usePermissions() {
    const page = usePage<PageProps>();
    
    const can = (permission?: string): boolean => {
        if (!permission) return true;
        return page.props.auth.permissions.includes(permission);
    };
    
    const hasAnyPermission = (...permissions: string[]): boolean => {
        return permissions.some(permission => can(permission));
    };
    
    const hasAllPermissions = (...permissions: string[]): boolean => {
        return permissions.every(permission => can(permission));
    };
    
    return {
        can,
        hasAnyPermission,
        hasAllPermissions
    };
}