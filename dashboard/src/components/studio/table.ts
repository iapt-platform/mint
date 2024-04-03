import { useIntl } from "react-intl";

export const PublicityValueEnum = () => {
  const intl = useIntl();
  return {
    all: {
      text: intl.formatMessage({
        id: "tables.publicity.all",
      }),
      status: "Default",
    },
    0: {
      text: intl.formatMessage({
        id: "tables.publicity.disable",
      }),
      status: "Default",
    },
    5: {
      text: intl.formatMessage({
        id: "tables.publicity.blocked",
      }),
      status: "Default",
    },
    10: {
      text: intl.formatMessage({
        id: "tables.publicity.private",
      }),
      status: "Success",
    },
    20: {
      text: intl.formatMessage({
        id: "tables.publicity.public.bylink",
      }),
      status: "Processing",
    },
    30: {
      text: intl.formatMessage({
        id: "tables.publicity.public",
      }),
      status: "Processing",
    },
    40: {
      text: intl.formatMessage({
        id: "tables.publicity.public.edit",
      }),
      status: "Processing",
    },
  };
};

export const RoleValueEnum = () => {
  const intl = useIntl();
  return {
    all: {
      text: intl.formatMessage({
        id: "tables.role.all",
      }),
    },
    owner: {
      text: intl.formatMessage({
        id: "tables.role.owner",
      }),
    },
    manager: {
      text: intl.formatMessage({
        id: "tables.role.manager",
      }),
    },
    editor: {
      text: intl.formatMessage({
        id: "tables.role.editor",
      }),
    },
    member: {
      text: intl.formatMessage({
        id: "tables.role.member",
      }),
    },
  };
};
