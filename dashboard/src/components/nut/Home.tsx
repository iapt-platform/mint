import ReactMarkdown from "react-markdown";
import code_png from "../../assets/nut/code.png";
import MdView from "../template/MdView";
import MarkdownForm from "./MarkdownForm";
import MarkdownShow from "./MarkdownShow";

const Widget = () => {
	return (
		<div>
			<h1>Home</h1>
			<h2>mardown test</h2>
			<MdView html="<h1 name='h1'>hello<MdTpl name='term'/></h1>" />
			<br />
			<MarkdownShow body="- Hello, **《mint》**!" />
			<br />
			<h3>Form</h3>
			<MarkdownForm />
			<br />
			<img alt="code" src={code_png} />
			<div>
				<ReactMarkdown>*This* is text with `quote`</ReactMarkdown>
			</div>
		</div>
	);
};

export default Widget;
