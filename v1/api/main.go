package main

import (
	"fmt"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/go-redis/redis/v8"
	"github.com/iapt-platform/mint"
)

func main() {

	opt, err := pg.ParseURL("postgres://postgres:@127.0.0.1:5432/mint?sslmode=disable")
	if err != nil {
		panic(err)
	}
	fmt.Println("pg connectd")
	db := pg.Connect(opt)
	defer db.Close()

	rdb := redis.NewClient(&redis.Options{
		Addr:     "localhost:6379",
		Password: "", // no password set
		DB:       0,  // use default DB
	})

	rt := gin.Default()

	rt.LoadHTMLGlob("templates/**/*")

	//课程 Courses
	//display a list of all Courses
	rt.GET("/api/courses", mint.CoursesIndex(db))
	//return an HTML form for creating a new Courses
	rt.GET("/courses/new", mint.CoursesNew(db))
	//create a new Courses
	rt.POST("/api/courses", mint.CoursesCreate(db))
	//display a specific Courses
	rt.GET("/api/courses/:id", mint.CoursesShow(db, rdb))
	//return an HTML form for edit a Courses
	rt.GET("/courses/:id/edit", mint.CoursesEdit(db))
	//update a specific Courses
	rt.PUT("/api/courses/:id", mint.CoursesUpdate(db, rdb))
	//delete a specific Courses
	rt.DELETE("/api/courses/:id", mint.CoursesDestroy(db, rdb))

	// Articles
	//display a list of all articles
	rt.GET("/api/articles", mint.ArticlesIndex(db))
	//return an HTML form for creating a new articles
	rt.GET("/api/articles/new", mint.ArticlesNew(db))
	//create a new articles
	rt.POST("/api/articles", mint.ArticlesCreate(db))
	//display a specific articles
	rt.GET("/api/articles/:id", mint.ArticlesShow(db, rdb))
	//return an HTML form for edit a articles
	rt.GET("/api/articles/:id/edit", mint.ArticlesEdit(db))
	//update a specific articles
	rt.PUT("/api/articles/:id", mint.ArticlesUpdate(db, rdb))
	//delete a specific articles
	rt.DELETE("/api/articles/:id", mint.ArticlesDestroy(db, rdb))

	//Collections
	//display a list of all collections
	rt.GET("/api/collections", mint.CollectionsIndex(db))
	//return an HTML form for creating a new collections
	rt.GET("/api/collections/new", mint.CollectionsNew(db))
	//create a new collections
	rt.POST("/api/collections", mint.CollectionsCreate(db))
	//display a specific collections
	rt.GET("/api/collections/:id", mint.CollectionsShow(db))
	//return an HTML form for edit a collections
	rt.GET("/api/collections/:id/edit", mint.CollectionsEdit(db))
	//update a specific collections
	rt.PUT("/api/collections/:id", mint.CollectionsUpdate(db))
	//delete a specific collections
	rt.DELETE("/api/collections/:id", mint.CollectionsDestroy(db))

	//文章列表article_lists

	//rt.GET("/api/article_list/collection/:cid",mint.GetCollectionArticleList(db))//改
	//修改
	//rt.POST("/api/article_list/article/:aid",mint.PostArticleListByArticle(db))//改
	//rt.DELETE("/api/article_list",mint.DeleteArticleInList(db,rdb))
	//rt.DELETE("/api/article_list/article/:aid",mint.DeleteArticleInList(db,rdb))
	//rt.DELETE("/api/article_list/collection/:cid",mint.DeleteCollectionInList(db,rdb))
	//Collections
	//display a list of all collections
	rt.GET("/api/article_list", mint.ArticleListIndex(db))
	//update a specific collections
	rt.PUT("/api/article_list/:id", mint.ArticleListUpdate(db))

	//group
	//display a list of all groups
	rt.GET("/api/groups", mint.GroupsIndex(db))
	//return an HTML form for creating a new group
	rt.GET("/api/groups/new", mint.GroupsNew(db))
	//create a new group
	rt.POST("/api/groups", mint.GroupsCreate(db))
	//display a specific Group
	rt.GET("/api/groups/:id", mint.GroupsShow(db, rdb))
	//return an HTML form for edit a group
	rt.GET("/api/groups/:id/edit", mint.GroupsEdit(db))
	//update a specific group
	rt.PUT("/api/groups/:id", mint.GroupsUpdate(db, rdb))
	//delete a specific group
	rt.DELETE("/api/groups/:id", mint.GroupsDestroy(db, rdb))

	//groups_users
	//display a list of all user in group
	rt.GET("/api/groups_users", mint.GroupsUsersIndex(db))
	//create a new user in group
	rt.POST("/api/groups_users", mint.GroupsUsersCreate(db))
	//delete a specific user in group
	rt.DELETE("/api/groups_users", mint.GroupsUsersDestroy(db, rdb))

	//channels
	//display a list of all channel
	rt.GET("/api/channels", mint.ChannelsIndex(db))
	//return an HTML form for creating a new group
	rt.GET("/api/channels/new", mint.ChannelsNew(db))
	//create a new channels
	rt.POST("/api/channels", mint.ChannelsCreate(db))
	//display a specific channels
	rt.GET("/api/channels/:id", mint.ChannelsShow(db, rdb))
	//return an HTML form for edit a channels
	rt.GET("/api/channels/:id/edit", mint.ChannelsEdit(db))
	//update a specific channels
	rt.PUT("/api/channels/:id", mint.ChannelsUpdate(db, rdb))
	//delete a specific channels
	rt.DELETE("/api/channels/:id", mint.ChannelsDestroy(db, rdb))

	//dictionaries
	//display a list of all channel
	rt.GET("/api/dictionaries", mint.DictionariesIndex(db))
	//return an HTML form for creating a new group
	rt.GET("/api/dictionaries/new", mint.DictionariesNew(db))
	//create a new channels
	rt.POST("/api/dictionaries", mint.DictionariesCreate(db))
	//display a specific channels
	rt.GET("/api/dictionaries/:id", mint.DictionariesShow(db, rdb))
	//return an HTML form for edit a channels
	rt.GET("/api/dictionaries/:id/edit", mint.DictionariesEdit(db))
	//update a specific channels
	rt.PUT("/api/dictionaries/:id", mint.DictionariesUpdate(db, rdb))
	//delete a specific channels
	rt.DELETE("/api/dictionaries/:id", mint.DictionariesDestroy(db, rdb))

	//ref_dicts
	//display a list of all ref_dicts
	rt.GET("/api/refdicts", mint.RefDictsIndex(db))
	//display a specific ref_dicts
	rt.GET("/api/refdicts/:id", mint.RefDictsShow(db, rdb))

	//terms
	//display a list of all terms
	rt.GET("/api/terms", mint.TermsIndex(db))
	//return an HTML form for creating a new terms
	rt.GET("/api/terms/new", mint.TermsNew(db))
	//create a new terms
	rt.POST("/api/terms", mint.TermsCreate(db))
	//display a specific terms
	rt.GET("/api/terms/:id", mint.TermsShow(db, rdb))
	//return an HTML form for edit a terms
	rt.GET("/api/terms/:id/edit", mint.TermsEdit(db))
	//update a specific terms
	rt.PUT("/api/terms/:id", mint.TermsUpdate(db, rdb))
	//delete a specific terms
	rt.DELETE("/api/terms/:id", mint.TermsDestroy(db, rdb))

	//active_logs
	//display a list of all terms
	rt.GET("/api/active", mint.ActiveIndex(db))
	//create a new active
	rt.POST("/api/active", mint.ActiveCreate(db))

	//sentences
	//display a list of all sentences
	rt.GET("/api/sentences", mint.SentencesIndex(db))
	//return an HTML form for creating a new sentences
	rt.GET("/api/sentences/new", mint.SentencesNew(db))
	//create a new sentences
	rt.POST("/api/sentences", mint.SentencesCreate(db))
	//display a specific sentences
	rt.GET("/api/sentences/:id", mint.SentencesShow(db, rdb))
	//return an HTML form for edit a sentences
	rt.GET("/api/sentences/:id/edit", mint.SentencesEdit(db))
	//update a specific sentences
	rt.PUT("/api/sentences/:id", mint.SentencesUpdate(db, rdb))
	//delete a specific sentences
	rt.DELETE("/api/sentences/:id", mint.SentencesDestroy(db, rdb))

	//sentences_historay
	//display a list of all sentences
	rt.GET("/api/senthis", mint.SentencesHistoraiesIndex(db))
	//create a new group
	rt.POST("/api/senthis", mint.SentencesHistoraiesCreate(db))
	//display a specific Group
	rt.GET("/api/senthis/:id", mint.SentencesHistoraiesShow(db, rdb))

	//authorizations
	//display a list of all authorizations
	rt.GET("/api/authorizations", mint.SentencesIndex(db))
	//return an HTML form for creating a new authorizations
	rt.GET("/api/authorizations/new", mint.SentencesNew(db))
	//create a new authorizations
	rt.POST("/api/authorizations", mint.SentencesCreate(db))
	//display a specific authorizations
	rt.GET("/api/authorizations/:id", mint.SentencesShow(db, rdb))
	//return an HTML form for edit a authorizations
	rt.GET("/api/authorizations/:id/edit", mint.SentencesEdit(db))
	//update a specific authorizations
	rt.PUT("/api/authorizations/:id", mint.SentencesUpdate(db, rdb))
	//delete a specific authorizations
	rt.DELETE("/api/authorizations/:id", mint.SentencesDestroy(db, rdb))

	//likes
	//display a list of all likes
	rt.GET("/api/likes", mint.LikesIndex(db))
	//create a new likes
	rt.POST("/api/likes", mint.LikesCreate(db))
	//delete a specific likes
	rt.DELETE("/api/likes/:id", mint.LikesDestroy(db))

	//books
	//display a list of all books
	rt.GET("/api/books", mint.BooksIndex(db))
	//return an HTML form for creating a new group
	rt.GET("/api/books/new", mint.BooksNew(db))
	//create a new books
	rt.POST("/api/books", mint.BooksCreate(db))
	//display a specific books
	rt.GET("/api/books/:id", mint.BooksShow(db, rdb))
	//return an HTML form for edit a books
	rt.GET("/api/books/:id/edit", mint.BooksEdit(db))
	//update a specific books
	rt.PUT("/api/books/:id", mint.BooksUpdate(db, rdb))
	//delete a specific books
	rt.DELETE("/api/books/:id", mint.BooksDestroy(db, rdb))

	//pali_text
	//display a list of all books
	rt.GET("/api/palitexts", mint.PaliTextsIndex(db))

	//wbw_templates 逐词解析模板
	//display a list of wbw_templates
	rt.GET("/api/wbwtemplates", mint.WbwTemplatesIndex(db))

	//wbw_templates
	//display a list of nissaya_book_maps
	rt.GET("/api/nissayabookmaps", mint.NissayaBookMapsIndex(db))

	//nissaya_page_maps
	//display a list of nissaya_page_maps
	rt.GET("/api/nissayapagemaps", mint.NissayaPageMapsIndex(db))

	//nissaya_page_maps
	//display a list of multi_edition_page_numbers
	rt.GET("/api/multi_edition_page_numbers", mint.MultiEditionPageNumbersIndex(db))

	//cs_para_numbers
	//display a list of cs_para_numbers
	rt.GET("/api/cs_para_numbers", mint.CsParaNumbersIndex(db))

	//pali_word_indexs
	//display a list of pali_word_indexs
	rt.GET("/api/pali_word_indexs", mint.PaliWordIndexsIndex(db))

	//pali_word_indexs
	//display a list of pali_word_indexs
	rt.GET("/api/pali_words", mint.PaliWordsIndex(db))

	//pali_word_indexs
	//display a list of pali_word_indexs
	rt.GET("/api/word_in_book_indexs", mint.WordInBookIndexsIndex(db))

	//bold
	//display a list of pali_word_indexs
	rt.GET("/api/bolds", mint.BoldsIndex(db))

	//sub book
	//display a list of subbooks
	rt.GET("/api/subbooks", mint.SubBooksIndex(db))

	//wbws
	//display a list of all wbws
	rt.GET("/api/wbws", mint.WbwsIndex(db))
	//return an HTML form for creating a new wbws
	rt.GET("/api/wbws/new", mint.WbwsNew(db))
	//create a new wbws
	rt.POST("/api/wbws", mint.WbwsCreate(db))
	//display a specific wbws
	rt.GET("/api/wbws/:id", mint.WbwsShow(db, rdb))
	//return an HTML form for edit a wbws
	rt.GET("/api/wbws/:id/edit", mint.WbwsEdit(db))
	//update a specific wbws
	rt.PUT("/api/wbws/:id", mint.WbwsUpdate(db, rdb))
	//delete a specific wbws
	rt.DELETE("/api/wbws/:id", mint.WbwsDestroy(db, rdb))

	//wbw_index
	//display a list of all wbwindexs
	rt.GET("/api/wbwindexs", mint.WbwIndexsIndex(db))
	//create a new wbwindexs
	rt.POST("/api/wbwindexs", mint.WbwIndexsCreate(db))
	//display a specific wbwindexs
	rt.GET("/api/wbwindexs/:id", mint.WbwIndexsShow(db, rdb))
	//delete a specific wbwindexs
	rt.DELETE("/api/wbwindexs/:id", mint.WbwIndexsDestroy(db, rdb))

	//wbws_lists
	//display a list of all wbws
	rt.GET("/api/wbws_lists", mint.WbwsListsIndex(db))
	//return an HTML form for creating a new wbws
	rt.GET("/api/wbws_lists/new", mint.WbwsListsNew(db))
	//create a new wbws
	rt.POST("/api/wbws_lists", mint.WbwsListsCreate(db))
	//display a specific wbws
	rt.GET("/api/wbws_lists/:id", mint.WbwsListsShow(db, rdb))
	//return an HTML form for edit a wbws
	rt.GET("/api/wbws_lists/:id/edit", mint.WbwsListsEdit(db))
	//update a specific wbws
	rt.PUT("/api/wbws_lists/:id", mint.WbwsListsUpdate(db, rdb))
	//delete a specific wbws
	rt.DELETE("/api/wbws_lists/:id", mint.WbwsListsDestroy(db, rdb))

	//comments
	//display a list of all comments
	rt.GET("/api/comments", mint.CommentsIndex(db))
	//return an HTML form for creating a new comments
	rt.GET("/api/comments/new", mint.CommentsNew(db))
	//create a new comments
	rt.POST("/api/comments", mint.CommentsCreate(db))
	//display a specific comments
	rt.GET("/api/comments/:id", mint.CommentsShow(db, rdb))
	//return an HTML form for edit a comments
	rt.GET("/api/comments/:id/edit", mint.CommentsEdit(db))
	//update a specific comments
	rt.PUT("/api/comments/:id", mint.CommentsUpdate(db, rdb))
	//delete a specific comments
	rt.DELETE("/api/comments/:id", mint.CommentsDestroy(db, rdb))

	rt.Run()
}
