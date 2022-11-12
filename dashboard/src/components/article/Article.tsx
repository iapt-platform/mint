import { message } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import { IArticleResponse } from "../api/Article";
import ArticleCard from "./ArticleCard";
import { IWidgetArticleData } from "./ArticleView";

interface IWidgetArticle {
	type?: string;
	articleId?: string;
	mode?: "read" | "edit";
}
const Widget = ({ type, articleId, mode }: IWidgetArticle) => {
	const [articleData, setArticleData] = useState<IWidgetArticleData>();

	useEffect(() => {
		if (typeof type !== "undefined" && typeof articleId !== "undefined") {
			get<IArticleResponse>(`/v2/corpus/${type}/${articleId}`).then(
				(json) => {
					if (json.ok) {
						setArticleData(json.data);
					} else {
						message.error(json.message);
					}
				}
			);
		}
	}, [type, articleId]);
	return <ArticleCard data={articleData} />;
};

export default Widget;
