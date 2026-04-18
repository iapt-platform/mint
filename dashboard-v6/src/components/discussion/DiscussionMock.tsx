import type { IComment, TDiscussionType, TResType } from "../../api/discussion";

export interface IAnswerCount {
  id: string;
  count: number;
}

interface IWidget {
  resId?: string;
  resType?: TResType;
  showTopicId?: string;
  focus?: string;
  type?: TDiscussionType;
  showStudent?: boolean;
  onTopicReady?: (value: IComment) => void;
}

const DiscussionWidget = ({
  resId,
  resType,
  showTopicId,
  showStudent = false,
  focus,
  type = "discussion",
}: IWidget) => {
  console.debug(
    "discussion mock",
    resId,
    resType,
    showTopicId,
    showStudent,
    focus,
    type
  );

  return <>discussion mock</>;
};

export default DiscussionWidget;
