import { Tag } from "antd";
import FooterBar from "../../components/library/FooterBar";

import HeadBar from "../../components/library/HeadBar";
import Home from "../../components/nut/Home";

const Widget = () => {
	return (
		<div>
			<HeadBar />
			<div>Home Page</div>
			<div>
				<Tag
					onClick={() => {
						console.log("test tag was clicked");
					}}
				>
					Test
				</Tag>
			</div>
			<div>
				<Home />
			</div>
			<FooterBar />
		</div>
	);
};

export default Widget;
