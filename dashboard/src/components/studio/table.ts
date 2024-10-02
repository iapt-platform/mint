import { useIntl } from "react-intl";

export const PublicityValueEnum = () => {
  const intl = useIntl();
  return {
    all: {
      text: intl.formatMessage({
        id: "forms.fields.publicity.all.label",
      }),
      status: "Default",
    },
    0: {
      text: intl.formatMessage({
        id: "forms.fields.publicity.disable.label",
      }),
      status: "Default",
    },
    5: {
      text: intl.formatMessage({
        id: "forms.fields.publicity.blocked.label",
      }),
      status: "Default",
    },
    10: {
      text: intl.formatMessage({
        id: "forms.fields.publicity.private.label",
      }),
      status: "Success",
    },
    20: {
      text: intl.formatMessage({
        id: "forms.fields.publicity.public_no_list.label",
      }),
      status: "Processing",
    },
    30: {
      text: intl.formatMessage({
        id: "forms.fields.publicity.public.label",
      }),
      status: "Processing",
    },
    40: {
      text: intl.formatMessage({
        id: "forms.fields.publicity.public.edit.label",
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
        id: "auth.role.all",
      }),
    },
    owner: {
      text: intl.formatMessage({
        id: "auth.role.owner",
      }),
    },
    manager: {
      text: intl.formatMessage({
        id: "auth.role.manager",
      }),
    },
    editor: {
      text: intl.formatMessage({
        id: "auth.role.editor",
      }),
    },
    member: {
      text: intl.formatMessage({
        id: "auth.role.member",
      }),
    },
  };
};
