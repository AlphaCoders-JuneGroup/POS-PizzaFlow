{{-- Pizza Customizer Modal --}}
<div class="modal fade" id="pizzaCustomizerModal" tabindex="-1" aria-labelledby="pizzaCustomizerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content pf-customizer-content text-dark">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fs-4 font-poppins" id="pizzaCustomizerModalLabel">Customize Your Pizza</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-2">
                <div class="row g-4">
                    {{-- Pizza Info & Preview --}}
                    <div class="col-md-5">
                        <div class="pf-customizer-preview-card text-center p-3 rounded h-100 d-flex flex-column justify-content-center">
                            <div class="pf-pizza-visual-container position-relative mb-3">
                                <img src="" id="custPizzaImage" alt="Pizza Preview" class="img-fluid rounded-circle pf-customizer-img shadow-lg" style="width: 200px; height: 200px; object-fit: cover;">
                                
                                {{-- Visual Topping Overlay indicators for premium feel --}}
                                <div class="pf-topping-visual-overlay"></div>
                            </div>
                            <h3 class="fs-4 mb-1" id="custPizzaName">Margherita Classic</h3>
                            <p class="text-muted small mb-2" id="custPizzaDesc">Fresh mozzarella, basil & tomato sauce.</p>
                            <div class="fs-5 text-pf-primary fw-bold">
                                Base Price: Rs. <span id="custBasePrice">1,890</span>
                            </div>
                        </div>
                    </div>

                    {{-- Customization Form Options --}}
                    <div class="col-md-7">
                        <form id="pizzaCustomizerForm" class="d-flex flex-column gap-4">
                            <input type="hidden" id="custPizzaId">
                            <input type="hidden" id="custPizzaSlug">

                            {{-- 1. Select Size --}}
                            <div>
                                <h4 class="fs-6 mb-2 border-bottom border-light-subtle pb-1 text-uppercase text-muted letter-spacing-1">1. Choose Size</h4>
                                <div class="d-flex flex-column gap-2" id="custSizeOptions">
                                    @foreach ($customizerData['sizes'] as $size)
                                        <label class="pf-customizer-option-card d-flex align-items-center justify-content-between p-2 rounded cursor-pointer">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="radio" name="pizza_size" value="{{ $size->name }}" data-price="{{ $size->price_modifier }}" @checked($loop->first)>
                                                <span>{{ $size->name }}</span>
                                            </div>
                                            <span class="text-muted small">
                                                {{ $size->price_modifier > 0 ? '+ Rs. ' . number_format($size->price_modifier) : 'Base' }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- 2. Select Crust --}}
                            <div>
                                <h4 class="fs-6 mb-2 border-bottom border-light-subtle pb-1 text-uppercase text-muted letter-spacing-1">2. Choose Crust</h4>
                                <div class="d-flex flex-column gap-2" id="custCrustOptions">
                                    @foreach ($customizerData['crusts'] as $crust)
                                        <label class="pf-customizer-option-card d-flex align-items-center justify-content-between p-2 rounded cursor-pointer">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="radio" name="pizza_crust" value="{{ $crust->name }}" data-price="{{ $crust->price_modifier }}" @checked($loop->first)>
                                                <span>{{ $crust->name }}</span>
                                            </div>
                                            <span class="text-muted small">
                                                {{ $crust->price_modifier > 0 ? '+ Rs. ' . number_format($crust->price_modifier) : 'Base' }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- 3. Select Sauce --}}
                            <div>
                                <h4 class="fs-6 mb-2 border-bottom border-light-subtle pb-1 text-uppercase text-muted letter-spacing-1">3. Choose Sauce</h4>
                                <select class="form-select pf-input text-dark bg-white border-light-subtle" name="pizza_sauce" id="custSauceOptions">
                                    @foreach ($customizerData['sauces'] as $sauce)
                                        <option value="{{ $sauce->name }}" data-price="{{ $sauce->price_modifier }}">
                                            {{ $sauce->name }} {{ $sauce->price_modifier > 0 ? '(+ Rs. ' . $sauce->price_modifier . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 4. Select Toppings --}}
                            <div>
                                <h4 class="fs-6 mb-2 border-bottom border-light-subtle pb-1 text-uppercase text-muted letter-spacing-1">4. Add Toppings</h4>
                                <div class="d-flex flex-column gap-3" id="custToppingsOptions">
                                    @foreach ($customizerData['toppings'] as $topping)
                                        <div class="pf-topping-row p-2 rounded">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="form-check">
                                                    <input class="form-check-input topping-checkbox" type="checkbox" value="{{ $topping->name }}" id="top-{{ $topping->_id }}" data-price="{{ $topping->price }}">
                                                    <label class="form-check-label text-dark animate-label" for="top-{{ $topping->_id }}">
                                                        {{ $topping->name }}
                                                    </label>
                                                </div>
                                                <span class="text-muted small">Rs. {{ $topping->price }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 bg-light d-flex justify-content-between align-items-center p-3">
                <div class="d-flex flex-column">
                    <span class="text-muted small">Total Price</span>
                    <span class="fs-3 fw-bold text-pf-primary font-poppins">Rs. <span id="custTotalPrice">1,890</span></span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-pf-primary px-4" id="custAddToCartBtn">
                        <i class="bi bi-cart-plus me-1"></i> Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hidden options configuration data block for JS access --}}
<div id="customizer-config-data" class="d-none" data-config="{{ json_encode($customizerData) }}"></div>
