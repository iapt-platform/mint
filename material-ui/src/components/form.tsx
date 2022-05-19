export const REAL_NAME_VALIDATOR = {
  required: true,
  minLength: 2,
  maxLength: 32,
};
export const NICK_NAME_VALIDATOR = {
  required: true,
  minLength: 2,
  maxLength: 32,
  pattern: /[\\.\w-]{6,32}/,
};
export const EMAIL_VALIDATOR = {
  required: true,
  minLength: 6,
  maxLength: 255,
  pattern: /^[\\.a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/,
};
export const PASSWORD_VALIDATOR = {
  required: true,
  minLength: 6,
  maxLength: 64,
};
