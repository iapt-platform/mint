import DictPreference, { IDictPreferenceWidget } from "../dict/DictPreference";

interface IWidgetTerm {
  props: string;
}
const DictPreferenceWidget = ({ props }: IWidgetTerm) => {
  const prop = JSON.parse(atob(props)) as IDictPreferenceWidget;
  return <DictPreference {...prop} />;
};

export default DictPreferenceWidget;
