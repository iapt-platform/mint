import { Typography } from "antd";
import MdView from "../../general/MdView";

interface IWidget {
  text?: string;
}
const { Text } = Typography;

const MdTranslation = ({ text }: IWidget) => {
  return (
    <Text className="sent_read_translation" style={{ display: "inline" }}>
      <MdView style={{ display: "inline" }} html={text} />
    </Text>
  );
};

export default MdTranslation;
