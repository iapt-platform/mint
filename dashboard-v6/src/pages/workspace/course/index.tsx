import { useIntl } from "react-intl";

import List from "../../../components/course/List";
import { useActiveStudio } from "../../../hooks/useActiveStudio";

const Widget = () => {
  const { studioName } = useActiveStudio();
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
