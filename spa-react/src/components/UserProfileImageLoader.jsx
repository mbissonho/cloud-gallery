import React, { useState } from "react";

const ImageLoader = ({ src, className, placeholderSrc, alt }) => {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);

  const handleImageLoad = () => {
    setLoading(false);
    setError(false);
  };

  const handleImageError = () => {
    setLoading(false);
    setError(true);
  };

  return (
    <>
      {error && (
        <img
          src={placeholderSrc}
          className={className}
          alt={alt}
          style={{ display: loading ? "none" : "block" }}
        />
      )}

      {!error && (
        <img
          src={src}
          className={className}
          alt={alt}
          onLoad={() => handleImageLoad(src)}
          onError={handleImageError}
          style={{ display: loading ? "none" : "block" }}
        />
      )}
    </>
  );
};

export default ImageLoader;
