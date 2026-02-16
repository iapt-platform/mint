import { useIntl } from "react-intl";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import { TSoftwareEdition } from "../api/Auth";

interface IWidget {
  style?: React.CSSProperties;
}
const SoftwareEdition = ({ style }: IWidget) => {
  const intl = useIntl();
  const user = useAppSelector(currentUser);
  let edition: TSoftwareEdition = "pro";
  if (user?.roles?.includes("basic")) {
    edition = "basic";
  }
  console.info("edition", edition);
  return (
    <span style={style}>
      {intl.formatMessage({
        id: `labels.software.edition.${edition}`,
      })}
    </span>
  );
};

export default SoftwareEdition;
