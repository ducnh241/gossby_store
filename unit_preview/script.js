(function () {
    var print_template_data = {
        "preview_config": [
            {
                "title": "Front",
                "dimension": {
                    "width": 1000,
                    "height": 1000
                },
                "config": {
                    "front": {
                        "position": {
                            "x": 153,
                            "y": 246
                        },
                        "dimension": {
                            "width": 463,
                            "height": 599
                        }
                    }
                }
            },
            {
                "title": "Back",
                "dimension": {
                    "width": 2000,
                    "height": 1000
                },
                "config": {
                    "back": {
                        "position": {
                            "x": 1000,
                            "y": 0
                        },
                        "dimension": {
                            "width": 2000,
                            "height": 1000
                        }
                    }
                }
            }
        ],
        "segments": {
            "front": {
                "builder_config": {
                    "dimension": {
                        "width": 1000,
                        "height": 1000
                    },
                    "view_box": {
                        "position": {
                            "x": 100,
                            "y": 100
                        },
                        "dimension": {
                            "width": 100,
                            "height": 100
                        }
                    },
                    "safe_box": {
                        "position": {
                            "x": 100,
                            "y": 100
                        },
                        "dimension": {
                            "width": 100,
                            "height": 100
                        }
                    },
                    "segment_place_config": {
                        "dimension": {
                            "width": "200",
                            "height": "200"
                        },
                        "position": {
                            "x": 100,
                            "y": 50
                        }
                    }
                },
                "dimension": {
                    'width': 766,
                    'height': 991
                },
                "title": "Front"
            },
            "back": {
                "builder_config": {
                    "dimension": {
                        "width": 1000,
                        "height": 1000
                    },
                    "view_box": {
                        "position": {
                            "x": 100,
                            "y": 100
                        },
                        "dimension": {
                            "width": 100,
                            "height": 100
                        }
                    },
                    "safe_box": {
                        "position": {
                            "x": 100,
                            "y": 100
                        },
                        "dimension": {
                            "width": 100,
                            "height": 100
                        }
                    },
                    "segment_place_config": {
                        "dimension": {
                            "width": 200,
                            "height": 200
                        },
                        "position": {
                            "x": 100,
                            "y": 50
                        }
                    }
                },
                "dimension": {
                    'width': 766,
                    'height': 991
                },
                "title": "Back"
            }
        },
        "print_file": {
            "default": {
                "dimension": {
                    "width": 2000,
                    "height": 1000
                },
                "config": {
                    "front": {
                        "position": {
                            "x": 0,
                            "y": 0
                        },
                        "dimension": {
                            "width": 2000,
                            "height": 1000
                        }
                    },
                    "back": {
                        "position": {
                            "x": 1000,
                            "y": 0
                        },
                        "dimension": {
                            "width": 2000,
                            "height": 1000
                        }
                    }
                }
            }
        }
    };

    var campaign_metadata = {
        "campaign_config": {
            "print_template_config": [
                {
                    "print_template_id": 1,
                    "segments": {
                        "front": {
                            "source": {
                                "type": "personalizedDesign",
                                "design_id": 100,
                                "option_default_values": {
                                    "7DCFF56456FGDFD": "Peter Blood",
                                    "HDISF7943534095": "34957349FDSFFDA67"
                                },
                                "position": {
                                    "x": 0,
                                    "y": 0
                                },
                                "dimension": {
                                    'width': 766,
                                    'height': 991
                                },
                                "rotation": 0
                            }
                        },
                        "back": {
                            "source": {
                                "type": "image",
                                "image_id": 10,
                                "position": {
                                    "x": 0,
                                    "y": 0
                                },
                                "dimension": {
                                    'width': 766,
                                    'height': 991
                                },
                                "rotation": 0
                            }
                        }
                    }
                },
                {
                    "print_template_id": 2,
                    "segments": {
                        "front": {
                            "source": {
                                "type": "personalizedDesign",
                                "design_id": 100,
                                "option_default_values": {
                                    "7DCFF56456FGDFD": "Peter Blood",
                                    "HDISF7943534095": "34957349FDSFFDA67"
                                },
                                "position": {
                                    "x": 0,
                                    "y": 0
                                },
                                "dimension": {
                                    "width": 1000,
                                    "height": 1000
                                },
                                "rotation": 0
                            }
                        },
                        "back": {
                            "source": {
                                "type": "image",
                                "image_id": 10,
                                "position": {
                                    "x": 0,
                                    "y": 0
                                },
                                "dimension": {
                                    "width": 1000,
                                    "height": 1000
                                },
                                "rotation": 0
                            }
                        }
                    }
                }
            ]
        }
    };

    function _renderPreview(preview_config, segment_configs, segment_sources) {
        var original_dimension = {
            width: 957,
            height: 1238
        };

        var canvas = $('<canvas />').appendTo(document.body).attr('width', original_dimension.width).attr('height', original_dimension.height)[0];

        var w = canvas.width;
        var h = canvas.height;

        ctx = canvas.getContext('2d');
        ctx.beginPath();
        ctx.moveTo(0, 0);
        ctx.lineTo(w, 0);
        ctx.lineTo(w, h);
        ctx.lineTo(0, h);
        ctx.closePath();
        ctx.fillStyle = 'yellow';
        ctx.fill();

        var preview = $('<div />').css({
            width: preview_config.dimension.width + 'px',
            height: preview_config.dimension.height + 'px'
        });

        canvas.toBlob(function (blob) {
            var url = URL.createObjectURL(blob);

            $(canvas).remove();

            $.each(preview_config.config, function (segment_key, config) {
                var segment_elm = $('<div />').css({
                    width: ((config.dimension.width / preview_config.dimension.width) * 100) + '%',
                    height: ((config.dimension.height / preview_config.dimension.height) * 100) + '%',
                    top: ((config.position.y / preview_config.dimension.height) * 100) + '%',
                    left: ((config.position.x / preview_config.dimension.width) * 100) + '%',
                    transform: 'rotate(' + (config.rotation ? config.rotation : 0) + 'deg)'
                }).addClass('segment').appendTo(preview);

                var img_container = $('<div />').addClass('img').css({
                    width: ((segment_sources[segment_key].source.dimension.width / segment_configs[segment_key].dimension.width) * 100) + '%',
                    height: ((segment_sources[segment_key].source.dimension.height / segment_configs[segment_key].dimension.height) * 100) + '%',
                    top: ((segment_sources[segment_key].source.position.y / segment_configs[segment_key].dimension.height) * 100) + '%',
                    left: ((segment_sources[segment_key].source.position.x / segment_configs[segment_key].dimension.width) * 100) + '%',
                    transform: 'rotate(' + (segment_sources[segment_key].source.rotation ? segment_sources[segment_key].source.rotation : 0) + 'deg)'
                }).appendTo(segment_elm);

                var image = $('<img />').attr('src', url).appendTo(img_container);
            });
        });

        return preview;
    }

    $('#scene').append(_renderPreview(print_template_data.preview_config[0], print_template_data.segments, campaign_metadata.campaign_config.print_template_config[0].segments));
})(jQuery);