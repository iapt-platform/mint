import { useMatches, useParams } from "react-router";
import { useIntl } from "react-intl";

import AnthologyTocEdit from "../../../components/anthology/AnthologyTocEdit";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const { anthologyId } = useParams(); //url 参数
  const user = useAppSelector(currentUser);
  const intl = useIntl();
  const matches = useMatches() as {
    data?: { title?: string; name?: string; word?: string };
  }[];
  const data = [...matches].reverse().find((m) => m.data)?.data;
  const name = data?.title ?? data?.name ?? data?.word;
  const prefix = intl.formatMessage({ id: "columns.studio.anthology.title" });

  return (
    <>
      <title>{name ? `${prefix}-${name}` : prefix}</title>
      <AnthologyTocEdit id={anthologyId} editorStudioName={user?.realName} />
    </>
  );
};

export default Widget;
