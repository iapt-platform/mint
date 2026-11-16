import { useMatches, useParams } from "react-router";
import { useIntl } from "react-intl";

import TagCreate from "../../../components/tag/TagCreate";
import { useActiveStudio } from "../../../hooks/useActiveStudio";

const Widget = () => {
  const { tagId } = useParams(); //url 参数
  const { studioName } = useActiveStudio();
  const intl = useIntl();
  const matches = useMatches() as {
    data?: { title?: string; name?: string; word?: string };
  }[];
  const data = [...matches].reverse().find((m) => m.data)?.data;
  const name = data?.title ?? data?.name ?? data?.word;
  const prefix = intl.formatMessage({ id: "columns.studio.tag.title" });

  return (
    <>
      <title>{name ? `${prefix}-${name}` : prefix}</title>
      <TagCreate studio={studioName} tagId={tagId} />
    </>
  );
};

export default Widget;
