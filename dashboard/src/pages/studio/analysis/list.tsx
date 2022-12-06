import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";

import { useState, useEffect } from "react";
import { Line } from "@ant-design/plots";

import { Calendar, momentLocalizer } from "react-big-calendar";
import moment from "moment";

const localizer = momentLocalizer(moment); // or globalizeLocalizer
const Widget = () => {
	const intl = useIntl(); //i18n
	const { studioname } = useParams(); //url 参数
	const [data, setData] = useState([]);

	useEffect(() => {
		asyncFetch();
	}, []);

	const myEventsList = [{ start: "2022-10-1", end: "2022-10-2" }];
	const asyncFetch = () => {
		fetch(
			"https://gw.alipayobjects.com/os/bmw-prod/1d565782-dde4-4bb6-8946-ea6a38ccf184.json"
		)
			.then((response) => response.json())
			.then((json) => setData(json))
			.catch((error) => {
				console.log("fetch data failed", error);
			});
	};
	const config = {
		data,
		xField: "Date",
		yField: "scales",
		xAxis: {
			tickCount: 5,
		},
		slider: {
			start: 0.1,
			end: 0.5,
		},
	};
	return (
		<>
			{studioname}
			<Line {...config} />

			<Calendar
				localizer={localizer}
				events={myEventsList}
				startAccessor="start"
				endAccessor="end"
			/>
		</>
	);
};

export default Widget;
