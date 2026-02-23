import type { IntlShape } from "react-intl";

import type { ProSchemaValueEnumObj } from "@ant-design/pro-components";

export const getSentIdInArticle = () => {
  const sentList: string[] = [];
  const sentElement = document.querySelectorAll(".pcd_sent");
  for (let index = 0; index < sentElement.length; index++) {
    const element = sentElement[index];
    const id = element.id.split("_")[1];
    sentList.push(id);
  }
  return sentList;
};

export const channelTypeFilter = (intl: IntlShape): ProSchemaValueEnumObj => {
  return {
    all: {
      text: intl.formatMessage({ id: "channel.type.all.title" }),
      status: "default",
    },
    translation: {
      text: intl.formatMessage({ id: "channel.type.translation.label" }),
      status: "success",
    },
    nissaya: {
      text: intl.formatMessage({ id: "channel.type.nissaya.label" }),
      status: "processing",
    },
    commentary: {
      text: intl.formatMessage({ id: "channel.type.commentary.label" }),
      status: "default",
    },
    original: {
      text: intl.formatMessage({ id: "channel.type.original.label" }),
      status: "default",
    },
  };
};
