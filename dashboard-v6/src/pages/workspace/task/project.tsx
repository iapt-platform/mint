import { useMatches, useNavigate, useParams } from "react-router";
import { useIntl } from "react-intl";

import ProjectWithTasks from "../../../components/task/ProjectWithTasks";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);

  const { projectId } = useParams();
  const navigate = useNavigate();
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
      <ProjectWithTasks
        studioName={currUser?.realName}
        projectId={projectId}
        onChange={(id: string) => {
          navigate(`/workspace/task/project/${id}`);
        }}
      />
    </>
  );
};

export default Widget;
