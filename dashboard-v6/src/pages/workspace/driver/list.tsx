import { useIntl } from "react-intl";
import AttachmentList from "../../../components/attachment/AttachmentList";
import { useActiveStudio } from "../../../hooks/useActiveStudio";

const Widget = () => {
  const { studioName } = useActiveStudio();
  const intl = useIntl();

  return (
    <div>
      <title>
        {intl.formatMessage({ id: "columns.studio.attachment.title" })}
      </title>
      <AttachmentList studioName={studioName} />
    </div>
  );
};

export default Widget;
