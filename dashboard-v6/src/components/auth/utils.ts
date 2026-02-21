export const getAvatarColor = (name?: string) => {
  const avatarColor = ["indianred", "blueviolet", "#87d068", "#108ee9"];
  if (!name) {
    return undefined;
  }
  let char = 0;
  if (name.length > 1) {
    char = name.length - 1;
  }
  const colorIndex = name.charCodeAt(char) % avatarColor.length;
  return avatarColor[colorIndex];
};
