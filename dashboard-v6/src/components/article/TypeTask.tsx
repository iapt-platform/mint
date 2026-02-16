import { ArticleMode, ArticleType } from "./Article";
import { ITaskData } from "../api/task";
import Task from "../task/Task";
import { openDiscussion } from "../discussion/DiscussionButton";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  onArticleChange?: (data: ITaskData) => void;
  onArticleEdit?: Function;
  onLoad?: (data: ITaskData) => void;
}
const TypeTask = ({
  type,
  channelId,
  articleId,
  mode = "read",
  onArticleChange,
  onLoad,
  onArticleEdit,
}: IWidget) => {
  return (
    <div>
      <Task
        taskId={articleId}
        onDiscussion={() => {
          articleId && openDiscussion(articleId, "task", false);
        }}
      />
    </div>
  );
};

export default TypeTask;
