import { useMatches, useNavigate, useParams } from "react-router";
import { useIntl } from "react-intl";

import TermEdit from "../../../components/term/TermEdit";

const Widget = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const intl = useIntl();
  const matches = useMatches() as {
    data?: { title?: string; name?: string; word?: string };
  }[];
  const data = [...matches].reverse().find((m) => m.data)?.data;
  const name = data?.title ?? data?.name ?? data?.word;
  const prefix = intl.formatMessage({ id: "columns.studio.term.title" });

  return (
    <>
      <title>{name ? `${prefix}-${name}` : prefix}</title>
      <TermEdit
        id={id}
        blockNavigation
        onUpdate={(saved) => {
          // 另存为会建出一条新术语，回到新那条的预览
          navigate(`/workspace/term/${saved.id ?? id}`);
        }}
        onCancel={() => {
          navigate(`/workspace/term/${id}`);
        }}
      />
    </>
  );
};

export default Widget;
