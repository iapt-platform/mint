import Project from "./Project";
import ProjectTask from "./ProjectTask";

interface IWidget {
  studioName?: string;
  projectId?: string;
  onChange?: (id: string) => void;
}
const ProjectWithTasks = ({ studioName, projectId, onChange }: IWidget) => {
  return (
    <>
      <Project
        studioName={studioName}
        projectId={projectId}
        onSelect={(id: string) => {
          onChange && onChange(id);
        }}
      />
      <ProjectTask studioName={studioName} projectId={projectId} />
    </>
  );
};
export default ProjectWithTasks;
