const removeNullAndBlankValues = (obj) => {
  return Object.fromEntries(
    Object.entries(obj).filter(
      ([key, value]) => value != null && value?.trim() !== ""
    )
  );
};
export default removeNullAndBlankValues;
