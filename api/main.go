package main

import (
	"github.com/gin-gonic/gin"

	"github.com/iapt-platform/mint"
)

func main() {
	db, err := mint.OpenDb()
	if err != nil {
		panic("failed to open database")
	}
	rt := gin.Default()

	// TODO 在这里进行http mount
	rt.GET("/demo/get", mint.GetDemo(db))

	rt.Run()
}
