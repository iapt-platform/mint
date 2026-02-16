import { Tooltip, Typography } from "antd";
import { Change, diffChars } from "diff";

const { Text } = Typography;

interface IWidget {
  content?: string | null;
  oldContent?: string | null;
  showToolTip?: boolean;
}
const TextDiffWidget = ({
  content,
  oldContent,
  showToolTip = true,
}: IWidget) => {
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
      return showToolTip ? (
        <Tooltip title={content}>
          <div>{diffResult}</div>
        </Tooltip>
      ) : (
        <div> {diffResult}</div>
      );
    } else {
      return <Text>{content}</Text>;
    }
  } else {
    return <></>;
  }
};

export default TextDiffWidget;
