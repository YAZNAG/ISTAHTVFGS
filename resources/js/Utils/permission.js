import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

export function usePermission(){
    const page = usePage();
    const permissions = computed(() => page.props.auth.permissions);

    const can = (permission) => permissions.value.includes(permission);

    const canAny = (permissionsArray) => {
        return permissionsArray.some(permission => can(permission));
    };

    const canAll = (permissionsArray) => {
        return permissionsArray.every(permission => can(permission));
    };

    return {can, canAny, canAll}
}