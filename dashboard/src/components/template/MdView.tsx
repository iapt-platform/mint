import { XmlToReact } from "./utilities";

interface IWidget {
	html: string;
}
const Widget = ({ html }: IWidget) => {
	const jsx = XmlToReact(html);
	return <>{jsx}</>;
};

export default Widget;
