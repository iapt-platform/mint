package mint

import (
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type PaliTextPath struct {
	Title     string `json:"title" `
	Book      int    `json:"book" `
	Paragraph int    `json:"paragraph" `
}

type PaliText struct {
	Id int `form:"id" json:"id" `

	BookId    int
	Paragraph int
	Level     int
	Class     string

	Text string
	Html string
	Toc  string

	StrLength     int
	ChapterLen    int
	NextChapter   int
	PrevChapter   int
	Parent        int
	ChapterStrlen int
	Path          []PaliTextPath

	Version int

	CreatedAt time.Time
	UpdatedAt time.Time
}

//display a list of all palitexts
func PaliTextsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		book := c.Query("book")

		// TODO 补充业务逻辑
		var palitexts []PaliText
		err := db.Model(&palitexts).Where("book = ?", book).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   palitexts,
		})
	}
}
