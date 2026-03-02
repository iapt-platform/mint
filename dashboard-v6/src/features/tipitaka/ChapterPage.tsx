import type { ArticleMode } from "../../api/Article";
import TypePali from "../../components/article/TypePali";
import SplitLayout from "../../components/general/SplitLayout";

interface IWidget {
  id?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
}
const Chapter = ({ id, mode, channelId }: IWidget) => {
  return (
    <SplitLayout key="mode-a" sidebarTitle="mint / deploy" sidebar={<></>}>
      {({ expandButton }) => (
        <TypePali
          id={id}
          type="chapter"
          mode={mode}
          channelId={channelId}
          headerExtra={expandButton}
        />
      )}
    </SplitLayout>
  );
};

export default Chapter;
