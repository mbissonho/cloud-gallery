import React, { useState, memo, useCallback } from "react";

const ImageLoaderComponent = ({ src, className, placeholderSrc, image }) => {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);

  const handleImageLoad = useCallback(() => {
    setLoading(false);
    setError(false);
  }, []);

  const handleImageError = useCallback(() => {
    setLoading(false);
    setError(true);
  }, []);

  return (
    <div>
      {loading && (
        <div
          style={{
            width: "200px",
            height: "133px",
            backgroundColor: "#f0f0f0",
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
          }}
        >
          ...
        </div>
      )}

      {error && (
        <img
          src={placeholderSrc}
          className={className}
          alt="Placeholder"
          style={{ display: loading ? "none" : "block" }}
          width="200"
          height="133"
        />
      )}

      {!error && (
        <img
          src={src}
          className={className}
          alt={image.title}
          onLoad={handleImageLoad}
          onError={handleImageError}
          style={{ display: loading ? "none" : "block" }}
          width="200"
          height="133"
        />
      )}
    </div>
  );
};

export default memo(ImageLoaderComponent);
