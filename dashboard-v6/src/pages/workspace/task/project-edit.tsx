import { useMatches, useParams } from "react-router";
import { useIntl } from "react-intl";

import ProjectEdit from "../../../components/task/ProjectEdit";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);

  const { projectId } = useParams();
  const intl = useIntl();
  const matches = useMatches() as {
    data?: { title?: string; name?: string; word?: string };
  }[];
  const data = [...matches].reverse().find((m) => m.data)?.data;
  const name = data?.title ?? data?.name ?? data?.word;
  const prefix = intl.formatMessage({ id: "pages.task.project.title" });

  return (
    <>
      <title>{name ? `${prefix}-${name}` : prefix}</title>
      <ProjectEdit studioName={currUser?.realName} projectId={projectId} />
    </>
  );
};

export default Widget;
