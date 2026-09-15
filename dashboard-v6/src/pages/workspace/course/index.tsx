import { useIntl } from "react-intl";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

import List from "../../../components/course/List";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  const intl = useIntl();
  return (
    <>
      <title>
        {intl.formatMessage({ id: "columns.studio.course.title" })}
      </title>
      <List studioName={studioName} />
    </>
  );
};

export default Widget;
