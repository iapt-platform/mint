import { PrimaryButton } from "@fluentui/react";
import HeadBar from "../components/library/HeadBar";

const Widget = () => {
	return (
		<div>
			<div>
				<HeadBar />
			</div>
			<div>
				<PrimaryButton
					onClick={() => {
						alert("aaa");
					}}
				>
					Demo
				</PrimaryButton>
			</div>
		</div>
	);
};

export default Widget;
