import { useParams } from "react-router-dom";
import BlogNav from "../../../components/library/blog/BlogNav";

const Widget = () => {
	// TODO
	const { studio } = useParams(); //url 参数

	return (
		<>
			<BlogNav selectedKey="anthology" studio={studio ? studio : ""} />
		</>
	);
};

export default Widget;
