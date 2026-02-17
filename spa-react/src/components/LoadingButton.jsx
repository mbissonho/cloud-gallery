const LoadingButton = ({
  children,
  onClick,
  isLoading,
  loadingText = "Loading...",
  className = "inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-red-500 sm:ml-3 sm:w-auto",
  ...props
}) => {
  const handleClick = async (event) => {
    if (onClick && !isLoading) {
      onClick(event);
    }
  };

  return (
    <button
      type="button"
      onClick={handleClick}
      className={`${className} ${
        isLoading ? "opacity-75 cursor-not-allowed" : ""
      }`}
      disabled={isLoading}
      {...props}
    >
      {isLoading ? (
        <span className="flex items-center">
          <svg
            className="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              className="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              strokeWidth="4"
            ></circle>
            <path
              className="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
          </svg>
          {loadingText?.length > 0 && loadingText}
        </span>
      ) : (
        children
      )}
    </button>
  );
};

export default LoadingButton;
