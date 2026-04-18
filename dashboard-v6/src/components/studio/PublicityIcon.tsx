import { GlobalOutlined, StopOutlined } from "@ant-design/icons";

import { LockIcon } from "../../assets/icon";
import type { TPublicity } from "./table";

interface IWidget {
  value?: TPublicity;
}
const PublicityIcon = ({ value }: IWidget) => {
  return value === "public" ? (
    <GlobalOutlined />
  ) : value === "private" ? (
    <LockIcon />
  ) : value === "disable" ? (
    <StopOutlined style={{ color: "red" }} />
  ) : (
    <></>
  );
};

export default PublicityIcon;
