import type { ArticleMode, ArticleType } from "./Article";
import type { ITaskData } from "../../api/task";
import Task from "../task/Task";
import { openDiscussion } from "../discussion/DiscussionButton";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  onArticleChange?: (data: ITaskData) => void;
  onLoad?: (data: ITaskData) => void;
}
const TypeTask = ({ articleId }: IWidget) => {
  return (
    <div>
      <Task
        taskId={articleId}
        onDiscussion={() => {
          if (articleId) {
            openDiscussion(articleId, "task", false);
          }
        }}
      />
    </div>
  );
};

export default TypeTask;
