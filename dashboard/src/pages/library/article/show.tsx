import { useState, useEffect } from "react";
import { useParams } from "react-router-dom";
import { message, Switch } from "antd";
import { Button, Drawer, Space } from "antd";
import { SettingOutlined } from "@ant-design/icons";

import { IArticleResponse } from "../../../components/api/Article";
import Article from "../../../components/article/Article";
import { IWidgetArticleData } from "../../../components/article/ArticleView";

import { get } from "../../../request";

/**
 * type:
 *   sent 句子
 *   sim  相似句
 *   v_para vri 自然段
 *   page  页码
 *   chapter 段落
 *   article 文章
 * @returns
 */
const Widget = () => {
	const { type, id, param } = useParams(); //url 参数
	const [open, setOpen] = useState(false);

	const showDrawer = () => {
		setOpen(true);
	};

	const onClose = () => {
		setOpen(false);
	};

	return (
		<div
			className="site-drawer-render-in-current-wrapper"
			style={{ display: "flex" }}
		>
			<div
				style={{ display: "flex", width: "100%", overflowX: "scroll" }}
			>
				<div>
					<Article type={type} articleId={id} />
				</div>
			</div>
			<div style={{ width: "2em" }}>
				<Button
					shape="circle"
					icon={<SettingOutlined />}
					onClick={showDrawer}
				></Button>
			</div>
			<Drawer
				title="Setting"
				placement="right"
				onClose={onClose}
				open={open}
				getContainer={false}
				style={{ position: "absolute" }}
			>
				<Space>
					保存到用户设置
					<Switch
						defaultChecked
						onChange={(checked) => {
							console.log(checked);
						}}
					/>
				</Space>
				<Space>
					显示原文
					<Switch
						defaultChecked
						onChange={(checked) => {
							console.log(checked);
						}}
					/>
				</Space>
				<Space>
					点词查询
					<Switch
						defaultChecked
						onChange={(checked) => {
							console.log(checked);
						}}
					/>
				</Space>
			</Drawer>
		</div>
	);
};

export default Widget;
