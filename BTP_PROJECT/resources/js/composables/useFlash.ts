// composables/useFlash.ts
import { usePage, router } from "@inertiajs/vue3";
import { toast } from "vue-sonner";

export interface ValidationErrors {
  [key: string]: string | string[];
}

export interface FlashProps {
  success?: string;
  error?: string;
  warning?: string;
  info?: string;
  errors?: ValidationErrors;
}

let initialized = false;
let lastFlash: string | null = null;

export function useFlash() {
  const page = usePage();

  if (!initialized) {
    router.on("finish", () => {
      const flash = page.props.flash as FlashProps; // 👈 cast ici
      if (!flash) return;

      // éviter duplication
      const current = JSON.stringify(flash);
      if (current === lastFlash) return;
      lastFlash = current;

      // Gestion des messages standards
      if (flash.success) toast.success(flash.success);
      if (flash.error) toast.error(flash.error);
      if (flash.warning) toast.warning?.(flash.warning);
      if (flash.info) toast.message?.(flash.info);

      // Gestion des erreurs de validation (withErrors)
      if (flash.errors) {
        Object.values(flash.errors).forEach((errMsg) => {
          if (typeof errMsg === "string") {
            toast.error(errMsg);
          } else if (Array.isArray(errMsg)) {
            errMsg.forEach((msg) => toast.error(msg));
          }
        });
      }

      // vider après consommation
      page.props.flash = {} as FlashProps;
    });

    initialized = true;
  }
}
