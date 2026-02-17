import { useTranslation } from "react-i18next";

export default function LoadingSpinner() {
    const { t } = useTranslation();

    return (
        <div className="flex justify-center items-center h-screen">
            <div className="w-12 h-12 border-4 border-blue-200 border-t-blue-500 rounded-full animate-spin"></div>
        </div>
    );
}
