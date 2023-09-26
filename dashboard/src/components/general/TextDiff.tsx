import { Tooltip, Typography } from "antd";
import { Change, diffChars } from "diff";

const { Text } = Typography;

interface IWidget {
  content?: string | null;
  oldContent?: string | null;
}
const TextDiffWidget = ({ content, oldContent }: IWidget) => {
  if (content) {
    if (oldContent) {
      const diff: Change[] = diffChars(oldContent, content);
      const diffResult = diff.map((item, id) => {
        return (
          <Text
            key={id}
            type={
              item.added ? "success" : item.removed ? "danger" : "secondary"
            }
            delete={item.removed ? true : undefined}
          >
            {item.value}
          </Text>
        );
      });
      return <Tooltip title={content}>{diffResult}</Tooltip>;
    } else {
      return <Text>{content}</Text>;
    }
  } else {
    return <></>;
  }
};

export default TextDiffWidget;
