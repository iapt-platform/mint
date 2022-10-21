import code_png from "../../assets/nut/code.png";
import MarkdownForm from "./MarkdownForm";
import MarkdownShow from "./MarkdownShow";

const Widget = () => {
  return (
    <div>
      <h1>Home</h1>
      <br />
      <MarkdownShow body="- Hello, **《mint》**!" />
      <br />
      <h3>Form</h3>
      <MarkdownForm />
      <br />
      <img alt="code" src={code_png} />
    </div>
  );
};

export default Widget;
