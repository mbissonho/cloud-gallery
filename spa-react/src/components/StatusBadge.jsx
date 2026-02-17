import React, { memo } from "react";

export function StatusBadgeComponent({ image }) {
  return (
    <span className="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 inset-ring inset-ring-gray-500/10">
      {image.status}
    </span>
  );
}

export const StatusBadge = memo(StatusBadgeComponent);

StatusBadge.displayName = "StatusBadge";
