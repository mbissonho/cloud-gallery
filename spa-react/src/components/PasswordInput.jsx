import { forwardRef, useState } from "react";
import { useTranslation } from "react-i18next";
import { EyeIcon, EyeSlashIcon } from "@heroicons/react/24/outline";

/**
 * Password input with a toggle button to reveal/hide the typed value.
 *
 * Forwards its ref to the underlying <input>, so it works both with
 * controlled inputs (value/onChange) and with react-hook-form's register().
 * Any extra props (id, name, value, onChange, autoComplete, disabled, ...)
 * are spread onto the input.
 */
const PasswordInput = forwardRef(function PasswordInput(
  { className = "", disabled, ...rest },
  ref,
) {
  const { t } = useTranslation("common");
  const [visible, setVisible] = useState(false);

  return (
    <div className="relative">
      <input
        ref={ref}
        type={visible ? "text" : "password"}
        disabled={disabled}
        className={`${className} pr-10`}
        {...rest}
      />
      <button
        type="button"
        onClick={() => setVisible((current) => !current)}
        disabled={disabled}
        aria-label={visible ? t("hide_password") : t("show_password")}
        aria-pressed={visible}
        className="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {visible ? (
          <EyeSlashIcon className="h-5 w-5" />
        ) : (
          <EyeIcon className="h-5 w-5" />
        )}
      </button>
    </div>
  );
});

export default PasswordInput;
