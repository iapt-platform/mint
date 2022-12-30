import { Card } from "antd";
import { useIntl } from "react-intl";
import SharedLinks from "../../../components/nut/users/NonSignInSharedLinks";

const Widget = () => {
  const intl = useIntl();

  return (
    <Card
      title={intl.formatMessage({
        id: "nut.users.sign-in.title",
      })}
    >
      sign up
      <br />
      <SharedLinks />
    </Card>
  );
};

export default Widget;
